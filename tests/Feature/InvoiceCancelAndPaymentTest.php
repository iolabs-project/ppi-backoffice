<?php

use App\Enums\ReceivablePaymentReferenceTypeEnum;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\Finance\AccountPayableService;
use App\Services\Finance\AccountReceivableService;
use App\Services\Finance\CashService;
use App\Services\JournalService;
use App\Services\Sales\SalesInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// A sales order with 500 down payment left and one invoice in the given status
function salesInvoiceFixture(string $status): array
{
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);
    $user = User::query()->create(['username' => 'tester', 'password' => 'password']);
    $customerID = DB::table('contacts')->insertGetId(['company_id' => $companyID, 'name' => 'Customer', 'is_customer' => true]);
    $warehouseID = DB::table('warehouses')->insertGetId(['company_id' => $companyID, 'name' => 'Gudang', 'code' => 'WH-001']);

    $salesOrderID = DB::table('sales_orders')->insertGetId([
        'company_id' => $companyID, 'customer_id' => $customerID, 'number' => 'SO-1', 'order_date' => now(),
        'status' => 'open', 'down_payment_amount' => 500, 'down_payment_remaining_amount' => 500, 'created_by' => $user->id,
    ]);
    $invoiceID = DB::table('sales_invoices')->insertGetId([
        'company_id' => $companyID, 'sales_order_id' => $salesOrderID, 'customer_id' => $customerID,
        'warehouse_id' => $warehouseID, 'number' => 'SI-1', 'invoice_date' => now(), 'status' => $status,
        'down_payment_amount' => 200, 'total_amount' => 1000, 'remaining_amount' => 1000, 'created_by' => $user->id,
    ]);

    return [$salesOrderID, $invoiceID];
}

test('cancelling a draft sales invoice leaves the sales order down payment alone', function () {
    [$salesOrderID, $invoiceID] = salesInvoiceFixture('draft');

    app(SalesInvoiceService::class)->cancelSalesInvoice($invoiceID);

    expect((float) DB::table('sales_orders')->where('id', $salesOrderID)->value('down_payment_remaining_amount'))->toBe(500.0)
        ->and(SalesInvoice::find($invoiceID))
        ->status->toBe('cancelled')
        ->remaining_amount->toBe(0.0);
});

test('payments are refused on draft and cancelled sales invoices', function (string $status) {
    [, $invoiceID] = salesInvoiceFixture($status);

    $request = new Request([
        'reference_type' => ReceivablePaymentReferenceTypeEnum::SALES_INVOICE->value,
        'amount' => 100,
    ]);

    expect(fn() => app(AccountReceivableService::class)->storePayment($request, $invoiceID))
        ->toThrow(ValidationException::class);
    expect(DB::table('receivable_payments')->count())->toBe(0);
})->with(['draft', 'cancelled']);

// A cash account holding 1000 and the payable/receivable account settings for the selected company
function paymentAccounts(): int
{
    $companyID = config('context.selected_company_id');
    Auth::login(User::query()->first());
    $categoryID = DB::table('account_categories')->insertGetId(['name' => 'Akun']);
    [$cashID, $payableID, $receivableID, $equityID] = collect(['1-1001', '2-1001', '1-1201', '3-1001'])
        ->map(fn($code) => DB::table('chart_of_accounts')->insertGetId([
            'company_id' => $companyID, 'category_id' => $categoryID, 'code' => $code, 'name' => $code,
        ]))
        ->all();
    DB::table('account_settings')->insert([
        ['company_id' => $companyID, 'account_id' => $payableID, 'setting_key' => 'account_payable'],
        ['company_id' => $companyID, 'account_id' => $receivableID, 'setting_key' => 'account_receivable'],
    ]);
    app(JournalService::class)->post(null, null, null, 'Modal awal', [
        ['account_id' => $cashID, 'debit' => 1000],
        ['account_id' => $equityID, 'credit' => 1000],
    ]);

    return $cashID;
}

function paymentRequest(string $type, int $invoiceID, int $accountID): Request
{
    return new Request([
        'reference_type' => $type, 'reference_id' => $invoiceID, 'account_id' => $accountID, 'amount' => 400,
        'payment_date' => '2026-10-27', 'payment_method' => 'cash', 'idempotency_key' => uniqid(),
    ]);
}

test('voiding a payable payment restores the expense, the cash balance and cancels its cash transaction', function () {
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);
    $user = User::query()->create(['username' => 'tester', 'password' => 'password']);
    $cashID = paymentAccounts();
    $expenseID = DB::table('expenses')->insertGetId([
        'company_id' => $companyID, 'number' => 'EXP-1', 'expense_date' => now(), 'status' => 'open',
        'total_amount' => 1000, 'remaining_amount' => 1000, 'created_by' => $user->id,
    ]);
    $service = app(AccountPayableService::class);

    $service->storePayment(paymentRequest('expense', $expenseID, $cashID), $expenseID);
    expect(app(CashService::class)->fetchAccountBalance($cashID))->toBe(600.0);

    $paymentID = DB::table('payable_payments')->value('id');
    $service->voidPayment($paymentID);

    $expense = DB::table('expenses')->find($expenseID);
    expect($expense->status)->toBe('open')
        ->and((float) $expense->remaining_amount)->toBe(1000.0)
        ->and(app(CashService::class)->fetchAccountBalance($cashID))->toBe(1000.0)
        ->and(DB::table('cash_transactions')->value('status'))->toBe('cancelled')
        ->and(DB::table('payable_payments')->value('voided_at'))->not->toBeNull();
    expect(fn() => $service->voidPayment($paymentID))->toThrow(ValidationException::class, 'sudah di-void');
});

test('voiding a receivable payment reopens the sales invoice and takes the cash back out', function () {
    [, $invoiceID] = salesInvoiceFixture('open');
    $cashID = paymentAccounts();
    $service = app(AccountReceivableService::class);

    $service->storePayment(paymentRequest(ReceivablePaymentReferenceTypeEnum::SALES_INVOICE->value, $invoiceID, $cashID), $invoiceID);
    expect(SalesInvoice::find($invoiceID)->status)->toBe('partial');

    $service->voidPayment(DB::table('receivable_payments')->value('id'));

    expect(SalesInvoice::find($invoiceID))
        ->status->toBe('open')
        ->remaining_amount->toBe(1000.0)
        ->and(app(CashService::class)->fetchAccountBalance($cashID))->toBe(1000.0)
        ->and(DB::table('cash_transactions')->value('status'))->toBe('cancelled');
});
