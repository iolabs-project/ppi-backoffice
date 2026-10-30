<?php

use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\User;
use App\Services\Finance\CashService;
use App\Services\JournalService;
use App\Services\Purchasing\GoodsReceiptService;
use App\Services\Purchasing\PurchaseOrderService;
use App\Services\Sales\DeliveryOrderService;
use App\Services\Sales\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

// Company, signed-in user (Super Admin unless told otherwise), contact, warehouse, product and two journal accounts
function cascadeBasics(bool $superAdmin = true): array
{
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);
    $user = User::query()->create(['username' => 'tester', 'password' => 'password']);
    if ($superAdmin) {
        $user->assignRole(Role::create(['name' => 'Super Admin', 'guard_name' => 'web']));
    }
    test()->actingAs($user);

    $contactID = DB::table('contacts')->insertGetId(['company_id' => $companyID, 'name' => 'Kontak', 'is_customer' => true]);
    $warehouseID = DB::table('warehouses')->insertGetId(['company_id' => $companyID, 'name' => 'Gudang', 'code' => 'WH-001']);
    $unitID = DB::table('units')->insertGetId(['name' => 'Pcs', 'symbol' => 'pcs']);
    $productCategoryID = DB::table('product_categories')->insertGetId(['company_id' => $companyID, 'name' => 'Umum']);
    $productID = DB::table('products')->insertGetId([
        'company_id' => $companyID, 'category_id' => $productCategoryID, 'unit_id' => $unitID,
        'name' => 'Produk', 'code' => 'P-1', 'batch_prefix' => 'P',
    ]);
    $categoryID = DB::table('account_categories')->insertGetId(['name' => 'Akun']);
    $accountIDs = collect(['1-1401', '5-1001'])
        ->map(fn($code) => DB::table('chart_of_accounts')->insertGetId([
            'company_id' => $companyID, 'category_id' => $categoryID, 'code' => $code, 'name' => $code,
        ]))
        ->all();

    return [$companyID, $user->id, $contactID, $warehouseID, $productID, $accountIDs];
}

// An open SO for 10 units with one finished DO that shipped 4 of them out of a 10-unit batch
function finishedDeliveryFixture(bool $superAdmin = true): array
{
    [$companyID, $userID, $customerID, $warehouseID, $productID, [$inventoryID, $cogsID]] = cascadeBasics($superAdmin);

    $salesOrderID = DB::table('sales_orders')->insertGetId([
        'company_id' => $companyID, 'customer_id' => $customerID, 'warehouse_id' => $warehouseID, 'number' => 'SO-1',
        'order_date' => now(), 'status' => 'open', 'created_by' => $userID,
    ]);
    $salesOrderItemID = DB::table('sales_order_items')->insertGetId([
        'sales_order_id' => $salesOrderID, 'product_id' => $productID, 'quantity' => 10, 'shipped_quantity' => 4, 'unit_price' => 1000,
    ]);
    $batchID = DB::table('product_batches')->insertGetId([
        'company_id' => $companyID, 'product_id' => $productID, 'batch_number' => 'B-1', 'initial_quantity' => 10, 'unit_cost' => 500,
    ]);
    DB::table('product_batch_stocks')->insert(['product_batch_id' => $batchID, 'warehouse_id' => $warehouseID, 'quantity' => 6, 'reserved_quantity' => 0]);
    DB::table('product_stocks')->insert(['company_id' => $companyID, 'product_id' => $productID, 'warehouse_id' => $warehouseID, 'quantity' => 6, 'average_unit_cost' => 500]);

    $deliveryOrderID = DB::table('delivery_orders')->insertGetId([
        'company_id' => $companyID, 'sales_order_id' => $salesOrderID, 'customer_id' => $customerID, 'warehouse_id' => $warehouseID,
        'number' => 'DO-1', 'delivery_date' => now(), 'status' => 'finished', 'created_by' => $userID,
    ]);
    DB::table('delivery_order_items')->insert([
        'delivery_order_id' => $deliveryOrderID, 'sales_order_item_id' => $salesOrderItemID, 'product_id' => $productID, 'quantity' => 4,
    ]);
    DB::table('inventory_transactions')->insert([
        'company_id' => $companyID, 'warehouse_id' => $warehouseID, 'product_id' => $productID, 'product_batch_id' => $batchID,
        'type' => 'sale', 'direction' => -1, 'quantity' => 4, 'unit_cost' => 500, 'total_cost' => 2000, 'stock_before' => 10, 'stock_after' => 6,
        'reference_type' => DeliveryOrder::class, 'reference_id' => $deliveryOrderID, 'transaction_date' => now(),
    ]);
    app(JournalService::class)->post(null, DeliveryOrder::class, $deliveryOrderID, 'DO-1', [
        ['account_id' => $cogsID, 'debit' => 2000],
        ['account_id' => $inventoryID, 'credit' => 2000],
    ]);

    return compact('companyID', 'userID', 'customerID', 'warehouseID', 'salesOrderID', 'salesOrderItemID', 'batchID', 'deliveryOrderID', 'inventoryID');
}

// An open PO for 10 units with one finished GR that received all of them into a new batch
function finishedReceiptFixture(): array
{
    [$companyID, $userID, $supplierID, $warehouseID, $productID, [$inventoryID, $grniID]] = cascadeBasics();

    $purchaseOrderID = DB::table('purchase_orders')->insertGetId([
        'company_id' => $companyID, 'supplier_id' => $supplierID, 'warehouse_id' => $warehouseID, 'number' => 'PO-1',
        'order_date' => now(), 'status' => 'closed', 'created_by' => $userID,
    ]);
    $purchaseOrderItemID = DB::table('purchase_order_items')->insertGetId([
        'purchase_order_id' => $purchaseOrderID, 'product_id' => $productID, 'quantity' => 10, 'received_quantity' => 10, 'unit_price' => 500,
    ]);
    $goodsReceiptID = DB::table('goods_receipts')->insertGetId([
        'company_id' => $companyID, 'purchase_order_id' => $purchaseOrderID, 'supplier_id' => $supplierID, 'warehouse_id' => $warehouseID,
        'number' => 'GR-1', 'receipt_date' => now(), 'status' => 'finished', 'created_by' => $userID,
    ]);
    $goodsReceiptItemID = DB::table('goods_receipt_items')->insertGetId([
        'goods_receipt_id' => $goodsReceiptID, 'purchase_order_item_id' => $purchaseOrderItemID, 'product_id' => $productID,
        'batch_number' => 'B-1', 'expected_quantity' => 10, 'shrinkage_quantity' => 0, 'received_quantity' => 10, 'unit_price' => 500, 'unit_cost' => 500,
    ]);
    $batchID = DB::table('product_batches')->insertGetId([
        'company_id' => $companyID, 'product_id' => $productID, 'goods_receipt_item_id' => $goodsReceiptItemID,
        'batch_number' => 'B-1', 'initial_quantity' => 10, 'unit_cost' => 500,
    ]);
    DB::table('product_batch_stocks')->insert(['product_batch_id' => $batchID, 'warehouse_id' => $warehouseID, 'quantity' => 10, 'reserved_quantity' => 0]);
    DB::table('product_stocks')->insert(['company_id' => $companyID, 'product_id' => $productID, 'warehouse_id' => $warehouseID, 'quantity' => 10, 'average_unit_cost' => 500]);
    app(JournalService::class)->post(null, GoodsReceipt::class, $goodsReceiptID, 'GR-1', [
        ['account_id' => $inventoryID, 'debit' => 5000],
        ['account_id' => $grniID, 'credit' => 5000],
    ]);

    return compact('purchaseOrderID', 'purchaseOrderItemID', 'goodsReceiptID', 'batchID', 'warehouseID', 'inventoryID');
}

test('cancelling an open SO returns the stock its finished delivery took', function () {
    $f = finishedDeliveryFixture();

    app(SalesOrderService::class)->changeSalesOrderStatus($f['salesOrderID'], 'cancelled');

    expect(DB::table('sales_orders')->where('id', $f['salesOrderID'])->value('status'))->toBe('cancelled')
        ->and(DB::table('delivery_orders')->where('id', $f['deliveryOrderID'])->value('status'))->toBe('cancelled')
        ->and((float) DB::table('sales_order_items')->where('id', $f['salesOrderItemID'])->value('shipped_quantity'))->toBe(0.0)
        ->and((float) DB::table('product_batch_stocks')->where('product_batch_id', $f['batchID'])->value('quantity'))->toBe(10.0)
        ->and((float) DB::table('product_stocks')->value('quantity'))->toBe(10.0)
        ->and(app(CashService::class)->fetchAccountBalance($f['inventoryID']))->toBe(0.0);
});

test('an SO whose invoice already has a payment cannot be cancelled', function () {
    $f = finishedDeliveryFixture();
    DB::table('sales_invoices')->insert([
        'company_id' => $f['companyID'], 'sales_order_id' => $f['salesOrderID'], 'customer_id' => $f['customerID'],
        'warehouse_id' => $f['warehouseID'], 'number' => 'SI-1', 'invoice_date' => now(), 'status' => 'partial',
        'total_amount' => 4000, 'remaining_amount' => 1000, 'created_by' => $f['userID'],
    ]);

    expect(fn() => app(SalesOrderService::class)->changeSalesOrderStatus($f['salesOrderID'], 'cancelled'))
        ->toThrow(ValidationException::class, 'Void pembayaran');
    expect(DB::table('delivery_orders')->where('id', $f['deliveryOrderID'])->value('status'))->toBe('finished')
        ->and((float) DB::table('product_batch_stocks')->where('product_batch_id', $f['batchID'])->value('quantity'))->toBe(6.0);
});

test('only Super Admin can cancel a finished delivery', function () {
    $f = finishedDeliveryFixture(superAdmin: false);

    expect(fn() => app(DeliveryOrderService::class)->changeDeliveryOrderStatus($f['deliveryOrderID'], 'cancelled'))
        ->toThrow(HttpException::class);
    expect(DB::table('delivery_orders')->where('id', $f['deliveryOrderID'])->value('status'))->toBe('finished');
});

test('cancelling a finished delivery reopens an SO that shipping had closed', function () {
    $f = finishedDeliveryFixture();
    DB::table('sales_orders')->where('id', $f['salesOrderID'])->update(['status' => 'closed']);

    app(DeliveryOrderService::class)->changeDeliveryOrderStatus($f['deliveryOrderID'], 'cancelled');

    expect(DB::table('sales_orders')->where('id', $f['salesOrderID'])->value('status'))->toBe('open')
        ->and((float) DB::table('sales_order_items')->where('id', $f['salesOrderItemID'])->value('shipped_quantity'))->toBe(0.0);
});

test('cancelling a finished goods receipt takes its batch back out and reopens the PO', function () {
    $f = finishedReceiptFixture();

    app(GoodsReceiptService::class)->changeGoodsReceiptStatus($f['goodsReceiptID'], 'cancelled');

    expect(DB::table('goods_receipts')->where('id', $f['goodsReceiptID'])->value('status'))->toBe('cancelled')
        ->and(DB::table('purchase_orders')->where('id', $f['purchaseOrderID'])->value('status'))->toBe('open')
        ->and((float) DB::table('purchase_order_items')->where('id', $f['purchaseOrderItemID'])->value('received_quantity'))->toBe(0.0)
        ->and((float) DB::table('product_batch_stocks')->where('product_batch_id', $f['batchID'])->value('quantity'))->toBe(0.0)
        ->and((float) DB::table('product_stocks')->value('quantity'))->toBe(0.0)
        ->and(app(CashService::class)->fetchAccountBalance($f['inventoryID']))->toBe(0.0);

    app(PurchaseOrderService::class)->changePurchaseOrderStatus($f['purchaseOrderID'], 'cancelled');
    expect(DB::table('purchase_orders')->where('id', $f['purchaseOrderID'])->value('status'))->toBe('cancelled');
});

test('a goods receipt whose batch was already used cannot be cancelled', function () {
    $f = finishedReceiptFixture();
    DB::table('product_batch_stocks')->where('product_batch_id', $f['batchID'])->update(['quantity' => 7]);

    expect(fn() => app(GoodsReceiptService::class)->changeGoodsReceiptStatus($f['goodsReceiptID'], 'cancelled'))
        ->toThrow(ValidationException::class, 'sudah terpakai');
    expect(DB::table('goods_receipts')->where('id', $f['goodsReceiptID'])->value('status'))->toBe('finished');
});
