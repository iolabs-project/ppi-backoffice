<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contacts')->insert([
            [
                'id' => 1,
                'company_id' => 1,
                'code' => 'EMP-001',
                'name' => 'Aldo Octavio Cahyadi',
                'email' => 'aldo.cahyadi@example.com',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh Alamat No. 123, Jakarta',
                'is_customer' => false,
                'is_supplier' => false,
                'is_employee' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'id' => 2,
                'company_id' => 1,
                'code' => 'EMP-002',
                'name' => 'Albert Irgi',
                'email' => 'albert.irgi@example.com',
                'phone' => '081234567891',
                'address' => 'Jl. Contoh Alamat No. 124, Jakarta',
                'is_customer' => false,
                'is_supplier' => false,
                'is_employee' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'company_id' => 1,
                'code' => 'SUP-001',
                'name' => 'PT. Supplier Contoh',
                'email' => 'supplier.contoh@example.com',
                'phone' => '081234567892',
                'address' => 'Jl. Contoh Alamat No. 125, Jakarta',
                'is_customer' => false,
                'is_supplier' => true,
                'is_employee' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'company_id' => 1,
                'code' => 'CUST-001',
                'name' => 'PT. Customer Contoh',
                'email' => 'customer.contoh@example.com',
                'phone' => '081234567893',
                'address' => 'Jl. Contoh Alamat No. 126, Jakarta',
                'is_customer' => true,
                'is_supplier' => false,
                'is_employee' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
