<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('warehouses')->insert([
            [
                'id' => 1,
                'company_id' => 1,
                'code' => 'WH1',
                'name' => 'Gudang Utama',
                'address' => 'Jl. Contoh Alamat No. 123, Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'code' => 'WH2',
                'name' => 'Gudang Cabang',
                'address' => 'Jl. Contoh Alamat No. 456, Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('units')->insert([
            [
                'id' => 1,
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Liter',
                'symbol' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('product_categories')->insert([
            [
                'id' => 1,
                'company_id' => 1,
                'name' => 'Bahan Baku',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'name' => 'Produk Jadi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('products')->insert([
            [
                'id' => 1,
                'company_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'name' => 'Jagung',
                'code' => 'JG001',
                'description' => 'Jagung kualitas premium',
                'minimum_stock' => 100,
                'inventory_account_id' => 101,
                'sales_account_id' => 102,
                'cogs_account_id' => 103,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'name' => 'Gula',
                'code' => 'GL001',
                'description' => 'Gula pasir putih',
                'minimum_stock' => 50,
                'inventory_account_id' => 101,
                'sales_account_id' => 102,
                'cogs_account_id' => 103,   
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('product_batches')->insert([
            [
                'id' => 1,
                'company_id' => 1,
                'product_id' => 1,
                'warehouse_id' => 1,
                'batch_number' => 'LOT001',
                'quantity' => 500,
                'reserved_quantity' => 0,
                'unit_cost' => 10000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'product_id' => 1,
                'warehouse_id' => 1,
                'batch_number' => 'LOT002',
                'quantity' => 200,
                'reserved_quantity' => 0,
                'unit_cost' => 10500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('inventory_transactions')->insert([
            [
                'id' => 1,
                'company_id' => 1,
                'product_id' => 1,
                'product_batch_id' => 1,
                'warehouse_id' => 1,
                'type' => 'opening',
                'direction' => 1,
                'quantity' => 500,
                'unit_cost' => 10000,
                'total_cost' => 5000000,
                'stock_before' => 0,
                'stock_after' => 500,
                'reference_type' => null,
                'reference_id' => null,
                'transaction_date' => now(),
                'note' => 'Stok awal untuk produk Jagung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'company_id' => 1,
                'product_id' => 1,
                'product_batch_id' => 2,
                'warehouse_id' => 1,
                'type' => 'opening',
                'direction' => 1,
                'quantity' => 200,
                'unit_cost' => 10500,
                'total_cost' => 2100000,
                'stock_before' => 500,
                'stock_after' => 700,
                'reference_type' => null,
                'reference_id' => null,
                'transaction_date' => now(),
                'note' => 'Stok awal tambahan untuk produk Jagung (batch kedua)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
