<?php

use App\Models\JournalEntry;
use App\Models\User;
use App\Services\DocumentTotals;
use App\Services\Finance\CashService;
use App\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// Company, signed-in user and two accounts for journal tests
function journalBasics(): array
{
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);
    Auth::login(User::query()->create(['username' => 'tester', 'password' => 'password']));
    $categoryID = DB::table('account_categories')->insertGetId(['name' => 'Aset']);
    $accountIDs = collect(['1-1001', '1-1002'])->map(fn($code) => DB::table('chart_of_accounts')->insertGetId([
        'company_id' => $companyID, 'category_id' => $categoryID, 'code' => $code, 'name' => $code,
    ]));

    return [$companyID, $accountIDs[0], $accountIDs[1]];
}

test('discounts and tax are rounded to whole rupiah like the forms', function () {
    expect(DocumentTotals::lineDiscount(3, 3333, 10))->toBe(1000.0)
        ->and(DocumentTotals::lineTotal(3, 3333, 10))->toBe(8999.0)
        ->and(DocumentTotals::percentOf(10005, 11))->toBe(1101.0)
        ->and(DocumentTotals::percentOf(1000, null))->toBe(0.0);
});

test('a journal whose sides differ only by float noise still posts', function () {
    [, $debitID, $creditID] = journalBasics();

    app(JournalService::class)->post(null, null, null, 'Uji', [
        ['account_id' => $debitID, 'debit' => 0.1],
        ['account_id' => $debitID, 'debit' => 0.2],
        ['account_id' => $creditID, 'credit' => 0.3],
    ]);

    expect(JournalEntry::count())->toBe(1);
});

test('a journal that really does not balance is refused', function () {
    [, $debitID, $creditID] = journalBasics();

    expect(fn() => app(JournalService::class)->post(null, null, null, 'Uji', [
        ['account_id' => $debitID, 'debit' => 1000],
        ['account_id' => $creditID, 'credit' => 900],
    ]))->toThrow(ValidationException::class);
});

test('a journal entry can only be reversed once', function () {
    [, $debitID, $creditID] = journalBasics();
    $journalService = app(JournalService::class);
    $journalService->post(null, null, null, 'Uji', [
        ['account_id' => $debitID, 'debit' => 500],
        ['account_id' => $creditID, 'credit' => 500],
    ]);
    $entryID = JournalEntry::value('id');

    $journalService->reverse($entryID, null, null);

    expect(fn() => $journalService->reverse($entryID, null, null))->toThrow(ValidationException::class);
    expect(JournalEntry::count())->toBe(2);
});

test('the cash account filter keeps the status filter', function () {
    [$companyID, $accountA, $accountB] = journalBasics();
    $row = fn(string $number, string $status, ?int $from, ?int $to) => [
        'company_id' => $companyID, 'number' => $number, 'transaction_date' => '2026-10-25',
        'type' => 'transfer', 'status' => $status, 'from_account_id' => $from, 'to_account_id' => $to,
        'created_by' => Auth::id(),
    ];
    DB::table('cash_transactions')->insert([
        $row('C-1', 'posted', $accountA, $accountB),
        $row('C-2', 'draft', $accountB, $accountA),
    ]);

    $result = app(CashService::class)->fetchTransactionTableData(new Request(['account_id' => $accountA, 'status' => 'posted']));

    expect(collect($result->items())->pluck('number')->all())->toBe(['C-1']);
});
