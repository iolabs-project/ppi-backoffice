<?php

use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

test('user activity log never contains the password', function () {
    $user = User::query()->create([
        'username' => 'log-user',
        'password' => Hash::make('secret-password'),
    ]);

    $user->update(['password' => Hash::make('another-password')]);

    $activities = Activity::query()->where('subject_type', User::class)->orderBy('id')->get();

    expect($activities)->toHaveCount(2);

    foreach ($activities as $activity) {
        expect($activity->log_name)->toBe('non_visible')
            ->and($activity->attribute_changes->get('attributes', []))->not->toHaveKey('password')
            ->and($activity->attribute_changes->get('old', []))->not->toHaveKey('password')
            ->and($activity->attribute_changes->get('attributes', []))->not->toHaveKey('remember_token');
    }

    expect($activities->first()->description)->toBe('Pengguna dibuat')
        ->and($activities->first()->attribute_changes->get('attributes'))->toHaveKey('username');
});

test('models keep their log name and description', function () {
    $company = Company::query()->forceCreate(['name' => 'PT Test', 'code' => 'PTT']);
    $warehouse = Warehouse::query()->create([
        'company_id' => $company->id,
        'name' => 'Gudang Utama',
        'code' => 'GD-01',
    ]);

    $activity = Activity::query()->where('subject_type', Warehouse::class)->latest('id')->first();

    expect($activity->log_name)->toBe('visible')
        ->and($activity->description)->toBe('Gudang dibuat')
        ->and($activity->subject_id)->toBe($warehouse->id)
        ->and($activity->attribute_changes->get('attributes'))->toHaveKey('name');
});
