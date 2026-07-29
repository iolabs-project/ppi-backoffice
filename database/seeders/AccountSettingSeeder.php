<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AccountSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Asset
            'cash'                      => 1,   // Kas
            'bank'                      => 2,   // Rekening Bank
            'account_receivable'        => 4,   // Piutang Usaha
            'inventory'                 => 7,   // Persediaan Barang
            'input_tax'                 => 14,  // PPN Masukan
            'purchase_down_payment'     => 13,  // Uang Muka Pembelian

            // Liability
            'account_payable'           => 34,  // Hutang Usaha
            'grni'                      => 35,  // Hutang Belum Ditagih
            'output_tax'                => 45,  // PPN Keluaran
            'sales_down_payment'        => 39,  // Uang Muka Penjualan

            // Equity
            'retained_earnings'         => 56,  // Laba Ditahan
            'opening_balance_equity'    => 59,  // Ekuitas Saldo Awal

            // Revenue
            'sales_revenue'             => 60,  // Pendapatan
            'sales_discount'            => 61,  // Diskon Penjualan
            'sales_return'              => 62,  // Retur Penjualan

            // Purchase / Inventory
            'cogs'                      => 64,  // Beban Pokok Pendapatan
            'purchase_discount'         => 65,  // Diskon Pembelian
            'purchase_return'           => 66,  // Retur Pembelian

            // Inventory Adjustment
            'inventory_adjustment_loss' => 134, // Penyesuaian Persediaan

            // Other Income / Expense
            'rounding_gain'             => 127, // Pembulatan
            'rounding_loss'             => 135, // Beban Lain-lain
        ];

        foreach ($settings as $settingKey => $accountId) {
            DB::table('account_settings')->insert([
                'company_id' => 1,
                'setting_key' => $settingKey,
                'account_id' => $accountId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
