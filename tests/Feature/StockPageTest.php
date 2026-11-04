<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

// Company with two warehouses and one product stocked in both, signed in with stock access
function stockBasics(): array
{
    $companyID = DB::table('companies')->insertGetId(['code' => 'PPI', 'name' => 'PPI']);
    config(['context.selected_company_id' => $companyID]);

    $user = User::query()->create(['username' => 'tester', 'password' => 'password']);
    $user->givePermissionTo(Permission::create(['name' => 'finances.stocks.view', 'guard_name' => 'web']));

    $unitID = DB::table('units')->insertGetId(['name' => 'Kilogram', 'symbol' => 'kg']);
    $categoryID = DB::table('product_categories')->insertGetId(['company_id' => $companyID, 'name' => 'Umum']);
    $productID = DB::table('products')->insertGetId([
        'company_id' => $companyID, 'category_id' => $categoryID, 'unit_id' => $unitID,
        'name' => 'Pupuk', 'code' => 'P-001', 'batch_prefix' => 'P',
    ]);
    $warehouseIDs = collect(['G1', 'G2'])->map(fn($code) => DB::table('warehouses')->insertGetId([
        'company_id' => $companyID, 'name' => "Gudang {$code}", 'code' => $code,
    ]));

    DB::table('product_stocks')->insert([
        ['company_id' => $companyID, 'product_id' => $productID, 'warehouse_id' => $warehouseIDs[0],
            'quantity' => 10, 'reserved_quantity' => 4, 'average_unit_cost' => 100],
        ['company_id' => $companyID, 'product_id' => $productID, 'warehouse_id' => $warehouseIDs[1],
            'quantity' => 30, 'reserved_quantity' => 0, 'average_unit_cost' => 200],
    ]);

    return [$user, $productID];
}

test('stock datatable totals every warehouse with a quantity weighted unit cost', function () {
    [$user, $productID] = stockBasics();

    $row = $this->actingAs($user)->getJson(route('finances.stocks.datatable'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->json('data.0');

    expect($row['id'])->toBe($productID)
        ->and($row['warehouse_count'])->toBe(2)
        ->and($row['quantity'])->toEqual(40)
        ->and($row['reserved_quantity'])->toEqual(4)
        ->and($row['available_quantity'])->toEqual(36)
        ->and($row['total_value'])->toEqual(7000)
        ->and($row['average_unit_cost'])->toEqual(175)
        ->and(collect($row['warehouses'])->pluck('average_unit_cost')->all())->toEqual([100, 200])
        ->and(collect($row['warehouses'])->pluck('total_value')->all())->toEqual([1000, 6000]);
});

test('stock page shows the inventory value summary', function () {
    [$user] = stockBasics();

    $this->actingAs($user)->get(route('finances.stocks.index'))
        ->assertOk()
        ->assertSee('7,000.00');
});

test('stock page requires the stock permission', function () {
    stockBasics();

    $this->actingAs(User::query()->create(['username' => 'other', 'password' => 'password']))
        ->getJson(route('finances.stocks.datatable'))
        ->assertForbidden();
});
