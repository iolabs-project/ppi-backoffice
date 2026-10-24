<?php

use App\Enums\PaymentTerm;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Master\ProductService;
use App\Services\NumberingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// Batch prefix = first letter + following consonants, padded from the remaining letters
test('batch prefix is derived from the product name', function (string $name, string $prefix) {
    expect(app(ProductService::class)->generateBatchPrefix($name))->toBe($prefix);
})->with([
    ['Katul', 'KTL'],
    ['Tepung Terigu', 'TPN'],
    ['Gula', 'GLU'],
    ['Jagung', 'JGN'],
    ['123', 'PRD'],
]);

// Master codes = config prefix + dash + counter, continuing from the highest numeric code
test('master data codes use the configured prefix and skip manual codes', function () {
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);
    $numbering = app(NumberingService::class);

    expect($numbering->masterCode('contact', Contact::class))->toBe('C-001')
        ->and($numbering->masterCode('warehouse', Warehouse::class))->toBe('WH-001')
        ->and($numbering->masterCode('product', Product::class))->toBe('PRD-001');

    foreach (['C-007', 'C-JKT', 'CUST-099'] as $code) {
        DB::table('contacts')->insert(['company_id' => $companyID, 'code' => $code, 'name' => $code]);
    }

    expect($numbering->masterCode('contact', Contact::class))->toBe('C-008');
});

// Batch number = product code + receipt date + counter that restarts per product per day
test('batch numbers use the product code, receipt date and a daily counter', function () {
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    $categoryID = DB::table('product_categories')->insertGetId(['company_id' => $companyID, 'name' => 'Bahan']);
    $unitID = DB::table('units')->insertGetId(['name' => 'Kilogram', 'symbol' => 'kg']);
    $productID = DB::table('products')->insertGetId([
        'company_id' => $companyID, 'category_id' => $categoryID, 'unit_id' => $unitID,
        'name' => 'Katul', 'code' => 'PRD-001', 'batch_prefix' => 'KTL',
    ]);
    $productService = app(ProductService::class);

    expect($productService->generateBatchNumber($productID, $companyID, '2026-10-25'))->toBe('PRD-001-261025-01');

    DB::table('product_batches')->insert(['company_id' => $companyID, 'product_id' => $productID, 'batch_number' => 'PRD-001-261025-01']);

    expect($productService->generateBatchNumber($productID, $companyID, '2026-10-25 14:30:00'))->toBe('PRD-001-261025-02')
        ->and($productService->generateBatchNumber($productID, $companyID, '2026-10-26'))->toBe('PRD-001-261026-01');
});

test('due date follows the payment term, manual keeps the picked date', function () {
    expect(PaymentTerm::dueDate('cash', '2026-10-22')->toDateString())->toBe('2026-10-22')
        ->and(PaymentTerm::dueDate('cod', '2026-10-22')->toDateString())->toBe('2026-10-22')
        ->and(PaymentTerm::dueDate('net_14', '2026-10-22')->toDateString())->toBe('2026-11-05')
        ->and(PaymentTerm::dueDate('custom', '2026-10-22', '2026-12-01'))->toBe('2026-12-01')
        ->and(PaymentTerm::dueDate(null, '2026-10-22'))->toBeNull();
});
