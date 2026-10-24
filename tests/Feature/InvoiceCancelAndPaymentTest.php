<?php

use App\Enums\ReceivablePaymentReferenceTypeEnum;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\Finance\AccountReceivableService;
use App\Services\Sales\SalesInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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
