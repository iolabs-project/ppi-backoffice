<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\put;

uses(RefreshDatabase::class);

function actingUserWithEditPermission(): User
{
    $user = User::query()->create([
        'username' => 'admin-user',
        'password' => Hash::make('old-password'),
    ]);
    $user->givePermissionTo(Permission::create([
        'name' => 'master.users.edit',
        'guard_name' => 'web',
    ]));

    Auth::login($user);

    return $user;
}

test('password change updates the password without touching the profile', function () {
    $user = actingUserWithEditPermission();

    put(route('master.users.password', $user->id), [
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertOk();

    $user->refresh();

    expect(Hash::check('brand-new-password', $user->password))->toBeTrue()
        ->and($user->username)->toBe('admin-user');
});

test('password change does not require username or role', function () {
    $user = actingUserWithEditPermission();

    put(route('master.users.password', $user->id), [
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertOk();
});

test('password change requires a confirmation that matches', function () {
    $user = actingUserWithEditPermission();

    put(route('master.users.password', $user->id), [
        'password' => 'brand-new-password',
        'password_confirmation' => 'something-else',
    ])->assertInvalid(['password_confirmation']);

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

test('password change requires a password', function () {
    $user = actingUserWithEditPermission();

    put(route('master.users.password', $user->id), [])
        ->assertInvalid(['password']);
});

test('password change enforces a minimum length', function () {
    $user = actingUserWithEditPermission();

    put(route('master.users.password', $user->id), [
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertInvalid(['password']);
});

test('user update leaves the password alone even when one is submitted', function () {
    $user = actingUserWithEditPermission();
    $originalHash = $user->password;
    $role = Role::create(['name' => 'operator', 'guard_name' => 'web']);

    put(route('master.users.update', $user->id), [
        'username' => 'renamed-user',
        'contact_id' => null,
        'role_id' => $role->id,
        'password' => 'sneaky-password',
        'password_confirmation' => 'sneaky-password',
    ]);

    $user->refresh();

    expect($user->username)->toBe('renamed-user')
        ->and($user->password)->toBe($originalHash)
        ->and(Hash::check('sneaky-password', $user->password))->toBeFalse();
});

test('user update requires a username and a role', function () {
    $user = actingUserWithEditPermission();

    put(route('master.users.update', $user->id), [])
        ->assertInvalid(['username', 'role_id']);
});
