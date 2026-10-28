<?php

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('overdue list skips expenses that are not due yet and counts days overdue', function () {
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    $userID = User::query()->create(['username' => 'tester', 'password' => 'password'])->id;
    $contactID = DB::table('contacts')->insertGetId(['company_id' => $companyID, 'name' => 'Vendor']);

    foreach (['EXP-LATE' => now()->subDays(5), 'EXP-LATER' => now()->addDays(10)] as $number => $dueDate) {
        DB::table('expenses')->insert([
            'company_id' => $companyID, 'number' => $number, 'contact_id' => $contactID, 'status' => 'open',
            'expense_date' => now()->subDays(20), 'due_date' => $dueDate->format('Y-m-d'),
            'total_amount' => 100, 'remaining_amount' => 100, 'created_by' => $userID,
        ]);
    }

    $overdue = app(DashboardService::class)->getOverdueInvoices($companyID);

    expect($overdue)->toHaveCount(1)
        ->and($overdue[0]['number'])->toBe('EXP-LATE')
        ->and($overdue[0]['days'])->toBe(5);
});
