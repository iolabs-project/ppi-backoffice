<?php

use App\Enums\SalesOrderStatus;
use App\Http\Requests\ExpenseFormRequest;
use App\Http\Requests\Finance\CashReceiveFormRequest;
use App\Http\Requests\Finance\CashSendFormRequest;
use App\Http\Requests\Finance\CashTransferFormRequest;
use App\Http\Requests\Purchasing\PurchaseOrderFormRequest;
use App\Http\Requests\Sales\DeliveryOrderFormRequest;
use App\Http\Requests\Sales\SalesOrderFormRequest;
use App\Models\User;
use App\Services\Purchasing\PurchaseOrderService;
use App\Services\Sales\DeliveryOrderService;
use App\Services\Sales\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// Company, user, customer and warehouse shared by the order fixtures
function orderBasics(): array
{
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);
    $user = User::query()->create(['username' => 'tester', 'password' => 'password']);
    $contactID = DB::table('contacts')->insertGetId(['company_id' => $companyID, 'name' => 'Customer', 'is_customer' => true]);
    $warehouseID = DB::table('warehouses')->insertGetId(['company_id' => $companyID, 'name' => 'Gudang', 'code' => 'WH-001']);

    return [$companyID, $user->id, $contactID, $warehouseID];
}

test('a status outside the expected ones is rejected instead of skipping validation', function (string $requestClass) {
    $request = $requestClass::create('/', 'POST', ['status' => 'closed']);

    expect(validator(['status' => 'closed'], $request->rules())->fails())->toBeTrue();
})->with([
    'sales order' => [SalesOrderFormRequest::class],
    'purchase order' => [PurchaseOrderFormRequest::class],
    'delivery order' => [DeliveryOrderFormRequest::class],
    'cash receive' => [CashReceiveFormRequest::class],
    'cash send' => [CashSendFormRequest::class],
    'cash transfer' => [CashTransferFormRequest::class],
    'expense' => [ExpenseFormRequest::class],
]);

test('a cash transfer needs two different accounts', function () {
    $input = ['status' => 'posted', 'from_account_id' => 5, 'to_account_id' => 5];
    $rules = CashTransferFormRequest::create('/', 'POST', $input)->rules();

    expect(validator($input, ['to_account_id' => array_diff($rules['to_account_id'], ['exists:chart_of_accounts,id'])])->fails())->toBeTrue();
});

test('cash receive does not accept extra costs', function () {
    $input = ['status' => 'posted', 'costs' => [['account_id' => 1, 'amount' => 5000]]];
    $rules = CashReceiveFormRequest::create('/', 'POST', $input)->rules();

    expect(validator($input, ['costs' => $rules['costs']])->fails())->toBeTrue();
});

test('order lines accept fractional quantities but not zero', function () {
    $rule = SalesOrderFormRequest::create('/', 'POST', ['status' => 'open'])->rules()['details.*.quantity'];

    expect(validator(['q' => 0.5], ['q' => $rule])->fails())->toBeFalse()
        ->and(validator(['q' => 0], ['q' => $rule])->fails())->toBeTrue();
});

test('a delivery order can only be created from an open sales order', function () {
    [$companyID, $userID, $customerID] = orderBasics();
    $salesOrderID = DB::table('sales_orders')->insertGetId([
        'company_id' => $companyID, 'customer_id' => $customerID, 'number' => 'SO-1',
        'order_date' => now(), 'status' => SalesOrderStatus::DRAFT->value, 'created_by' => $userID,
    ]);

    expect(fn() => app(DeliveryOrderService::class)->storeDeliveryOrder(new Request(['sales_order_id' => $salesOrderID])))
        ->toThrow(ValidationException::class);
    expect(DB::table('delivery_orders')->count())->toBe(0);
});

test('opening an empty draft order from the list is refused', function (string $table, string $service, string $method) {
    [$companyID, $userID] = orderBasics();
    $orderID = DB::table($table)->insertGetId([
        'company_id' => $companyID, 'number' => 'X-1', 'order_date' => now(), 'status' => 'draft', 'created_by' => $userID,
    ]);

    expect(fn() => app($service)->{$method}($orderID, 'open'))->toThrow(ValidationException::class);
    expect(DB::table($table)->where('id', $orderID)->value('status'))->toBe('draft');
})->with([
    'sales order' => ['sales_orders', SalesOrderService::class, 'changeSalesOrderStatus'],
    'purchase order' => ['purchase_orders', PurchaseOrderService::class, 'changePurchaseOrderStatus'],
]);

test('an open sales order with a delivery cannot be cancelled', function () {
    [$companyID, $userID, $customerID, $warehouseID] = orderBasics();
    $salesOrderID = DB::table('sales_orders')->insertGetId([
        'company_id' => $companyID, 'customer_id' => $customerID, 'number' => 'SO-1',
        'order_date' => now(), 'status' => SalesOrderStatus::OPEN->value, 'created_by' => $userID,
    ]);
    DB::table('delivery_orders')->insert([
        'company_id' => $companyID, 'sales_order_id' => $salesOrderID, 'customer_id' => $customerID,
        'warehouse_id' => $warehouseID, 'number' => 'DO-1', 'delivery_date' => now(), 'status' => 'draft', 'created_by' => $userID,
    ]);

    expect(fn() => app(SalesOrderService::class)->changeSalesOrderStatus($salesOrderID, SalesOrderStatus::CANCELLED->value))
        ->toThrow(ValidationException::class);
    expect(DB::table('sales_orders')->where('id', $salesOrderID)->value('status'))->toBe(SalesOrderStatus::OPEN->value);
});
