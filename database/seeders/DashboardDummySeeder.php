<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardDummySeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;
        $userId = 1;
        $warehouseId = 1;
        $now = Carbon::now();

        // ============ Additional Contacts ============
        $newContacts = [
            ['code' => 'CUST-003', 'name' => 'PT Roti Sumber Rejeki', 'is_customer' => true, 'is_supplier' => false],
            ['code' => 'CUST-004', 'name' => 'CV Mie Mas Joko', 'is_customer' => true, 'is_supplier' => false],
            ['code' => 'CUST-005', 'name' => 'Toko Bahan Kue Anggrek', 'is_customer' => true, 'is_supplier' => false],
            ['code' => 'CUST-006', 'name' => 'PT Catering Selera Nusantara', 'is_customer' => true, 'is_supplier' => false],
            ['code' => 'SUP-002', 'name' => 'PT Bogasari Flour Mills', 'is_customer' => false, 'is_supplier' => true],
            ['code' => 'SUP-003', 'name' => 'CV Gula Manis Lestari', 'is_customer' => false, 'is_supplier' => true],
            ['code' => 'SUP-004', 'name' => 'PT Salim Ivomas', 'is_customer' => false, 'is_supplier' => true],
        ];

        $contactIds = [];
        foreach ($newContacts as $c) {
            $id = DB::table('contacts')->insertGetId(array_merge($c, [
                'company_id' => $companyId,
                'email' => strtolower(str_replace([' ', '.'], '-', $c['name'])) . '@example.com',
                'phone' => '0812' . rand(1000000, 9999999),
                'address' => 'Jl. Contoh No. ' . rand(1, 100),
                'transportation_cost' => 0,
                'is_employee' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
            $contactIds[$c['code']] = $id;
        }

        $customerIds = [4, 5, $contactIds['CUST-003'], $contactIds['CUST-004'], $contactIds['CUST-005'], $contactIds['CUST-006']];
        $vendorIds = [3, $contactIds['SUP-002'], $contactIds['SUP-003'], $contactIds['SUP-004']];

        // ============ Additional Products ============
        $newProducts = [
            ['code' => 'TPG-001', 'name' => 'Tepung Terigu 25kg', 'bp' => 'TPG'],
            ['code' => 'GLL-001', 'name' => 'Gula Pasir 50kg', 'bp' => 'GLL'],
            ['code' => 'MNY-001', 'name' => 'Minyak Goreng 20L', 'bp' => 'MNY'],
            ['code' => 'MGR-001', 'name' => 'Margarin 1kg', 'bp' => 'MGR'],
            ['code' => 'TLR-001', 'name' => 'Telur Ayam 1kg', 'bp' => 'TLR'],
        ];

        $productIds = [1, 2]; // existing products from InventorySeeder
        foreach ($newProducts as $p) {
            $id = DB::table('products')->insertGetId([
                'company_id' => $companyId,
                'code' => $p['code'],
                'name' => $p['name'],
                'batch_prefix' => $p['bp'],
                'category_id' => 1,
                'unit_id' => 1,
                'minimum_stock' => 10,
                'description' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $productIds[] = $id;
        }

        // Counters
        $soNum = 1; $siNum = 1; $doNum = 1;
        $poNum = 1; $piNum = 1; $grNum = 1;
        $expNum = 1; $jeNum = 1;

        // ============ Generate 12 months of data ============
        for ($monthOff = 11; $monthOff >= 0; $monthOff--) {
            $month = $now->copy()->subMonths($monthOff);

            // --- Sales pipeline ---
            $soCount = rand(3, 6);
            for ($i = 0; $i < $soCount; $i++) {
                $custId = $customerIds[array_rand($customerIds)];
                $orderDate = $month->copy()->startOfMonth()->addDays(rand(0, 25));
                $productId = $productIds[array_rand($productIds)];
                $qty = rand(10, 50);
                $unitPrice = rand(80, 300) * 10000;
                $total = $qty * $unitPrice;

                // Status: older months are closed/paid
                if ($monthOff > 4) {
                    $soSt = 'closed'; $doSt = 'finished'; $siSt = 'paid';
                } elseif ($monthOff > 1) {
                    $soSt = ['closed', 'open'][rand(0, 1)];
                    $doSt = 'finished';
                    $siSt = ['paid', 'open'][rand(0, 1)];
                } else {
                    $soSt = 'open';
                    $doSt = ['finished', 'draft'][rand(0, 1)];
                    $siSt = ['open', 'partial'][rand(0, 1)];
                }

                // Sales Order
                $soId = DB::table('sales_orders')->insertGetId([
                    'company_id' => $companyId, 'customer_id' => $custId,
                    'warehouse_id' => $warehouseId, 'sales_person_id' => null,
                    'number' => 'SO-' . $now->format('Y') . '-' . str_pad($soNum++, 4, '0', STR_PAD_LEFT),
                    'order_date' => $orderDate->format('Y-m-d'),
                    'due_date' => $orderDate->copy()->addDays(14)->format('Y-m-d'),
                    'status' => $soSt,
                    'subtotal' => $total, 'total_amount' => $total,
                    'created_by' => $userId, 'created_at' => $orderDate, 'updated_at' => $orderDate,
                ]);

                // SO Item
                $soItemId = DB::table('sales_order_items')->insertGetId([
                    'sales_order_id' => $soId, 'product_id' => $productId,
                    'quantity' => $qty, 'unit_price' => $unitPrice, 'total_amount' => $total,
                    'created_at' => $orderDate, 'updated_at' => $orderDate,
                ]);

                // Delivery Order
                $doDate = $orderDate->copy()->addDays(rand(1, 5));
                $doId = DB::table('delivery_orders')->insertGetId([
                    'company_id' => $companyId, 'sales_order_id' => $soId,
                    'customer_id' => $custId, 'warehouse_id' => $warehouseId,
                    'number' => 'DO-' . $now->format('Y') . '-' . str_pad($doNum++, 4, '0', STR_PAD_LEFT),
                    'delivery_date' => $doDate->format('Y-m-d'),
                    'status' => $doSt,
                    'subtotal' => $total, 'total_amount' => $total,
                    'created_by' => $userId, 'created_at' => $doDate, 'updated_at' => $doDate,
                ]);

                $doItemId = DB::table('delivery_order_items')->insertGetId([
                    'delivery_order_id' => $doId, 'sales_order_item_id' => $soItemId,
                    'product_id' => $productId, 'quantity' => $qty,
                    'created_at' => $doDate, 'updated_at' => $doDate,
                ]);

                // Sales Invoice
                $invDate = $orderDate->copy()->addDays(rand(3, 7));
                $remaining = $siSt === 'paid' ? 0 : ($siSt === 'partial' ? round($total * 0.5) : $total);

                $siId = DB::table('sales_invoices')->insertGetId([
                    'company_id' => $companyId, 'sales_order_id' => $soId,
                    'customer_id' => $custId, 'warehouse_id' => $warehouseId,
                    'number' => 'INV-' . $now->format('Y') . '-' . str_pad($siNum++, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => $invDate->format('Y-m-d'),
                    'due_date' => $invDate->copy()->addDays(14)->format('Y-m-d'),
                    'status' => $siSt,
                    'subtotal' => $total, 'total_amount' => $total, 'remaining_amount' => $remaining,
                    'created_by' => $userId, 'created_at' => $invDate, 'updated_at' => $invDate,
                ]);

                DB::table('sales_invoice_items')->insert([
                    'sales_invoice_id' => $siId, 'sales_order_item_id' => $soItemId,
                    'delivery_order_item_id' => $doItemId,
                    'product_id' => $productId, 'quantity' => $qty,
                    'unit_price' => $unitPrice, 'total_amount' => $total,
                    'created_at' => $invDate, 'updated_at' => $invDate,
                ]);

                // Journal: Revenue (Debit Piutang[4], Credit Pendapatan[60])
                $jeId = DB::table('journal_entries')->insertGetId([
                    'company_id' => $companyId,
                    'number' => 'JE-' . $now->format('Y') . '-' . str_pad($jeNum++, 5, '0', STR_PAD_LEFT),
                    'journal_date' => $invDate->format('Y-m-d'),
                    'description' => 'Penjualan',
                    'status' => 'posted',
                    'created_by' => $userId, 'created_at' => $invDate, 'updated_at' => $invDate,
                ]);

                DB::table('journal_entry_items')->insert([
                    ['journal_entry_id' => $jeId, 'account_id' => 4, 'debit' => $total, 'credit' => 0, 'created_at' => $invDate, 'updated_at' => $invDate],
                    ['journal_entry_id' => $jeId, 'account_id' => 60, 'debit' => 0, 'credit' => $total, 'created_at' => $invDate, 'updated_at' => $invDate],
                ]);

                // Journal: COGS (Debit HPP[64], Credit Persediaan[7])
                $cogs = round($total * 0.65);
                $jeId2 = DB::table('journal_entries')->insertGetId([
                    'company_id' => $companyId,
                    'number' => 'JE-' . $now->format('Y') . '-' . str_pad($jeNum++, 5, '0', STR_PAD_LEFT),
                    'journal_date' => $invDate->format('Y-m-d'),
                    'description' => 'Harga Pokok Penjualan',
                    'status' => 'posted',
                    'created_by' => $userId, 'created_at' => $invDate, 'updated_at' => $invDate,
                ]);

                DB::table('journal_entry_items')->insert([
                    ['journal_entry_id' => $jeId2, 'account_id' => 64, 'debit' => $cogs, 'credit' => 0, 'created_at' => $invDate, 'updated_at' => $invDate],
                    ['journal_entry_id' => $jeId2, 'account_id' => 7, 'debit' => 0, 'credit' => $cogs, 'created_at' => $invDate, 'updated_at' => $invDate],
                ]);
            }

            // --- Purchase pipeline ---
            $poCount = rand(2, 4);
            for ($i = 0; $i < $poCount; $i++) {
                $vendorId = $vendorIds[array_rand($vendorIds)];
                $orderDate = $month->copy()->startOfMonth()->addDays(rand(0, 25));
                $productId = $productIds[array_rand($productIds)];
                $qty = rand(20, 100);
                $unitPrice = rand(50, 200) * 10000;
                $total = $qty * $unitPrice;

                $poSt = $monthOff > 4 ? 'closed' : 'open';
                $piSt = $monthOff > 4 ? 'paid' : 'open';

                // Purchase Order
                $poId = DB::table('purchase_orders')->insertGetId([
                    'company_id' => $companyId, 'supplier_id' => $vendorId,
                    'warehouse_id' => $warehouseId,
                    'number' => 'PO-' . $now->format('Y') . '-' . str_pad($poNum++, 4, '0', STR_PAD_LEFT),
                    'order_date' => $orderDate->format('Y-m-d'),
                    'due_date' => $orderDate->copy()->addDays(14)->format('Y-m-d'),
                    'status' => $poSt,
                    'subtotal' => $total, 'total_amount' => $total,
                    'created_by' => $userId, 'created_at' => $orderDate, 'updated_at' => $orderDate,
                ]);

                $poItemId = DB::table('purchase_order_items')->insertGetId([
                    'purchase_order_id' => $poId, 'product_id' => $productId,
                    'quantity' => $qty, 'unit_price' => $unitPrice,
                    'subtotal' => $total, 'total_amount' => $total,
                    'created_at' => $orderDate, 'updated_at' => $orderDate,
                ]);

                // Goods Receipt
                $grDate = $orderDate->copy()->addDays(rand(2, 5));
                $grId = DB::table('goods_receipts')->insertGetId([
                    'company_id' => $companyId, 'purchase_order_id' => $poId,
                    'supplier_id' => $vendorId, 'warehouse_id' => $warehouseId,
                    'number' => 'GR-' . $now->format('Y') . '-' . str_pad($grNum++, 4, '0', STR_PAD_LEFT),
                    'receipt_date' => $grDate->format('Y-m-d'),
                    'status' => 'finished', 'subtotal' => $total,
                    'created_by' => $userId, 'created_at' => $grDate, 'updated_at' => $grDate,
                ]);

                $grItemId = DB::table('goods_receipt_items')->insertGetId([
                    'goods_receipt_id' => $grId, 'purchase_order_item_id' => $poItemId,
                    'product_id' => $productId,
                    'expected_quantity' => $qty, 'shrinkage_quantity' => 0, 'received_quantity' => $qty,
                    'unit_price' => $unitPrice, 'subtotal' => $total,
                    'unit_cost' => $unitPrice, 'total_amount' => $total,
                    'created_at' => $grDate, 'updated_at' => $grDate,
                ]);

                // Purchase Invoice
                $piDate = $orderDate->copy()->addDays(rand(3, 7));
                $piRemaining = $piSt === 'paid' ? 0 : $total;

                $piId = DB::table('purchase_invoices')->insertGetId([
                    'company_id' => $companyId, 'purchase_order_id' => $poId,
                    'supplier_id' => $vendorId, 'warehouse_id' => $warehouseId,
                    'number' => 'PI-' . $now->format('Y') . '-' . str_pad($piNum++, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => $piDate->format('Y-m-d'),
                    'due_date' => $piDate->copy()->addDays(14)->format('Y-m-d'),
                    'status' => $piSt,
                    'subtotal' => $total, 'total_amount' => $total, 'remaining_amount' => $piRemaining,
                    'created_by' => $userId, 'created_at' => $piDate, 'updated_at' => $piDate,
                ]);

                DB::table('purchase_invoice_items')->insert([
                    'purchase_invoice_id' => $piId, 'purchase_order_item_id' => $poItemId,
                    'goods_receipt_item_id' => $grItemId, 'product_id' => $productId,
                    'quantity' => $qty, 'unit_price' => $unitPrice,
                    'subtotal' => $total, 'total_amount' => $total,
                    'created_at' => $piDate, 'updated_at' => $piDate,
                ]);

                // Journal: Purchase (Debit Persediaan[7], Credit Hutang Usaha[34])
                $jeId = DB::table('journal_entries')->insertGetId([
                    'company_id' => $companyId,
                    'number' => 'JE-' . $now->format('Y') . '-' . str_pad($jeNum++, 5, '0', STR_PAD_LEFT),
                    'journal_date' => $piDate->format('Y-m-d'),
                    'description' => 'Pembelian barang',
                    'status' => 'posted',
                    'created_by' => $userId, 'created_at' => $piDate, 'updated_at' => $piDate,
                ]);

                DB::table('journal_entry_items')->insert([
                    ['journal_entry_id' => $jeId, 'account_id' => 7, 'debit' => $total, 'credit' => 0, 'created_at' => $piDate, 'updated_at' => $piDate],
                    ['journal_entry_id' => $jeId, 'account_id' => 34, 'debit' => 0, 'credit' => $total, 'created_at' => $piDate, 'updated_at' => $piDate],
                ]);
            }

            // --- Expenses ---
            $expCount = rand(1, 3);
            for ($i = 0; $i < $expCount; $i++) {
                $expDate = $month->copy()->startOfMonth()->addDays(rand(1, 25));
                $amount = rand(2, 15) * 1_000_000;

                $expId = DB::table('expenses')->insertGetId([
                    'company_id' => $companyId,
                    'contact_id' => $vendorIds[array_rand($vendorIds)],
                    'number' => 'EXP-' . $now->format('Y') . '-' . str_pad($expNum++, 4, '0', STR_PAD_LEFT),
                    'expense_date' => $expDate->format('Y-m-d'),
                    'due_date' => $expDate->copy()->addDays(7)->format('Y-m-d'),
                    'status' => 'paid',
                    'subtotal' => $amount, 'total_amount' => $amount, 'remaining_amount' => 0,
                    'created_by' => $userId, 'created_at' => $expDate, 'updated_at' => $expDate,
                ]);

                DB::table('expense_items')->insert([
                    'expense_id' => $expId, 'account_id' => 77, // Gaji
                    'description' => 'Biaya operasional ' . $month->format('M Y'),
                    'amount' => $amount,
                    'created_at' => $expDate, 'updated_at' => $expDate,
                ]);

                // Journal: Expense (Debit Gaji[77], Credit Kas[1])
                $jeId = DB::table('journal_entries')->insertGetId([
                    'company_id' => $companyId,
                    'number' => 'JE-' . $now->format('Y') . '-' . str_pad($jeNum++, 5, '0', STR_PAD_LEFT),
                    'journal_date' => $expDate->format('Y-m-d'),
                    'description' => 'Beban operasional',
                    'status' => 'posted',
                    'created_by' => $userId, 'created_at' => $expDate, 'updated_at' => $expDate,
                ]);

                DB::table('journal_entry_items')->insert([
                    ['journal_entry_id' => $jeId, 'account_id' => 77, 'debit' => $amount, 'credit' => 0, 'created_at' => $expDate, 'updated_at' => $expDate],
                    ['journal_entry_id' => $jeId, 'account_id' => 1, 'debit' => 0, 'credit' => $amount, 'created_at' => $expDate, 'updated_at' => $expDate],
                ]);
            }

            // --- Cash deposit for paid invoices (Debit Kas[1], Credit Piutang[4]) ---
            if ($monthOff > 4) {
                $depositAmount = rand(50, 150) * 1_000_000;
                $jeId = DB::table('journal_entries')->insertGetId([
                    'company_id' => $companyId,
                    'number' => 'JE-' . $now->format('Y') . '-' . str_pad($jeNum++, 5, '0', STR_PAD_LEFT),
                    'journal_date' => $month->copy()->addDays(20)->format('Y-m-d'),
                    'description' => 'Penerimaan piutang',
                    'status' => 'posted',
                    'created_by' => $userId, 'created_at' => $month, 'updated_at' => $month,
                ]);

                DB::table('journal_entry_items')->insert([
                    ['journal_entry_id' => $jeId, 'account_id' => 1, 'debit' => $depositAmount, 'credit' => 0, 'created_at' => $month, 'updated_at' => $month],
                    ['journal_entry_id' => $jeId, 'account_id' => 4, 'debit' => 0, 'credit' => $depositAmount, 'created_at' => $month, 'updated_at' => $month],
                ]);
            }
        }

        // ============ Opening balance ============
        $openDate = $now->copy()->subMonths(11)->startOfMonth();
        $jeId = DB::table('journal_entries')->insertGetId([
            'company_id' => $companyId,
            'number' => 'JE-' . $now->format('Y') . '-' . str_pad($jeNum++, 5, '0', STR_PAD_LEFT),
            'journal_date' => $openDate->format('Y-m-d'),
            'description' => 'Saldo awal kas',
            'status' => 'posted',
            'created_by' => $userId, 'created_at' => $openDate, 'updated_at' => $openDate,
        ]);

        DB::table('journal_entry_items')->insert([
            ['journal_entry_id' => $jeId, 'account_id' => 1, 'debit' => 500_000_000, 'credit' => 0, 'created_at' => $openDate, 'updated_at' => $openDate],
            ['journal_entry_id' => $jeId, 'account_id' => 59, 'debit' => 0, 'credit' => 500_000_000, 'created_at' => $openDate, 'updated_at' => $openDate],
        ]);

        // ============ Overdue invoices (for dashboard alert) ============
        $this->createOverdueInvoice($companyId, $warehouseId, $userId, $customerIds, $productIds, $soNum++, $doNum++, $siNum++, $jeNum++, $now->copy()->subDays(25), 42_000_000);
        $this->createOverdueInvoice($companyId, $warehouseId, $userId, $customerIds, $productIds, $soNum++, $doNum++, $siNum++, $jeNum++, $now->copy()->subDays(40), 28_500_000);

        $this->command->info('✅ Dashboard dummy data seeded!');
        $this->command->info('   - ' . count($newContacts) . ' additional contacts');
        $this->command->info('   - ' . count($newProducts) . ' additional products');
        $this->command->info('   - 12 months of sales/purchase/expense transactions');
        $this->command->info('   - Journal entries for all transactions');
        $this->command->info('   - 2 overdue invoices for dashboard alerts');
    }

    private function createOverdueInvoice(
        int $companyId, int $warehouseId, int $userId,
        array $customerIds, array $productIds,
        int $soNum, int $doNum, int $siNum, int $jeNum,
        Carbon $date, int $amount
    ): void {
        $custId = $customerIds[array_rand($customerIds)];
        $productId = $productIds[array_rand($productIds)];
        $qty = (int) ceil($amount / 2_000_000);
        $unitPrice = (int) ($amount / $qty);

        $soId = DB::table('sales_orders')->insertGetId([
            'company_id' => $companyId, 'customer_id' => $custId,
            'warehouse_id' => $warehouseId,
            'number' => 'SO-' . $date->format('Y') . '-' . str_pad($soNum, 4, '0', STR_PAD_LEFT),
            'order_date' => $date->format('Y-m-d'),
            'due_date' => $date->copy()->addDays(14)->format('Y-m-d'),
            'status' => 'open',
            'subtotal' => $amount, 'total_amount' => $amount,
            'created_by' => $userId, 'created_at' => $date, 'updated_at' => $date,
        ]);

        $soItemId = DB::table('sales_order_items')->insertGetId([
            'sales_order_id' => $soId, 'product_id' => $productId,
            'quantity' => $qty, 'unit_price' => $unitPrice, 'total_amount' => $amount,
            'created_at' => $date, 'updated_at' => $date,
        ]);

        $doId = DB::table('delivery_orders')->insertGetId([
            'company_id' => $companyId, 'sales_order_id' => $soId,
            'customer_id' => $custId, 'warehouse_id' => $warehouseId,
            'number' => 'DO-' . $date->format('Y') . '-' . str_pad($doNum, 4, '0', STR_PAD_LEFT),
            'delivery_date' => $date->copy()->addDays(2)->format('Y-m-d'),
            'status' => 'finished',
            'subtotal' => $amount, 'total_amount' => $amount,
            'created_by' => $userId, 'created_at' => $date, 'updated_at' => $date,
        ]);

        $doItemId = DB::table('delivery_order_items')->insertGetId([
            'delivery_order_id' => $doId, 'sales_order_item_id' => $soItemId,
            'product_id' => $productId, 'quantity' => $qty,
            'created_at' => $date, 'updated_at' => $date,
        ]);

        DB::table('sales_invoices')->insert([
            'company_id' => $companyId, 'sales_order_id' => $soId,
            'customer_id' => $custId, 'warehouse_id' => $warehouseId,
            'number' => 'INV-' . $date->format('Y') . '-' . str_pad($siNum, 4, '0', STR_PAD_LEFT),
            'invoice_date' => $date->copy()->addDays(3)->format('Y-m-d'),
            'due_date' => $date->copy()->addDays(17)->format('Y-m-d'),
            'status' => 'open',
            'subtotal' => $amount, 'total_amount' => $amount, 'remaining_amount' => $amount,
            'created_by' => $userId, 'created_at' => $date, 'updated_at' => $date,
        ]);

        // Journal
        $jeId = DB::table('journal_entries')->insertGetId([
            'company_id' => $companyId,
            'number' => 'JE-' . $date->format('Y') . '-' . str_pad($jeNum, 5, '0', STR_PAD_LEFT),
            'journal_date' => $date->format('Y-m-d'),
            'description' => 'Penjualan overdue',
            'status' => 'posted',
            'created_by' => $userId, 'created_at' => $date, 'updated_at' => $date,
        ]);

        DB::table('journal_entry_items')->insert([
            ['journal_entry_id' => $jeId, 'account_id' => 4, 'debit' => $amount, 'credit' => 0, 'created_at' => $date, 'updated_at' => $date],
            ['journal_entry_id' => $jeId, 'account_id' => 60, 'debit' => 0, 'credit' => $amount, 'created_at' => $date, 'updated_at' => $date],
        ]);
    }
}
