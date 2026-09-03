<?php

namespace App\Services;

use App\Enums\AccountCategoryEnum;
use App\Enums\JournalEntryStatusEnum;
use App\Enums\SalesInvoiceStatus;
use App\Enums\PurchaseInvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\DeliveryOrderStatus;
use App\Models\ChartOfAccount;
use App\Models\CashTransaction;
use App\Models\DeliveryOrder;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all dashboard data.
     */
    public static function getData(int $companyId): array
    {
        $service = new self();

        return [
            'kpis'             => $service->getKpis($companyId),
            'monthly'          => $service->getMonthlyChart($companyId),
            'pipeline'         => $service->getPipeline($companyId),
            'recentActivities' => $service->getRecentActivities($companyId),
            'overdueInvoices'  => $service->getOverdueInvoices($companyId),
            'topContacts'      => $service->getTopContacts($companyId),
            'greetingStats'    => $service->getGreetingStats($companyId),
        ];
    }

    /**
     * KPI cards: Pendapatan, Pengeluaran, Laba Bersih, Saldo Kas
     */
    public function getKpis(int $companyId): array
    {
        $now = now();
        $currentStart = $now->copy()->startOfMonth()->format('Y-m-d');
        $currentEnd = $now->copy()->endOfMonth()->format('Y-m-d');
        $prevStart = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $prevEnd = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        // Revenue (from posted journal entries in REVENUE category)
        $revenueCurrent = $this->getCategoryTotal($companyId, AccountCategoryEnum::REVENUE->value, $currentStart, $currentEnd, false);
        $revenuePrev = $this->getCategoryTotal($companyId, AccountCategoryEnum::REVENUE->value, $prevStart, $prevEnd, false);

        // Expenses (COGS + Operating Expense + Other Expense)
        $expenseCategories = [
            AccountCategoryEnum::COST_OF_GOODS_SOLD->value,
            AccountCategoryEnum::OPERATING_EXPENSE->value,
            AccountCategoryEnum::OTHER_EXPENSE->value,
        ];
        $expenseCurrent = 0;
        $expensePrev = 0;
        foreach ($expenseCategories as $catId) {
            $expenseCurrent += $this->getCategoryTotal($companyId, $catId, $currentStart, $currentEnd, true);
            $expensePrev += $this->getCategoryTotal($companyId, $catId, $prevStart, $prevEnd, true);
        }

        // Net profit
        $otherIncomeCurrent = $this->getCategoryTotal($companyId, AccountCategoryEnum::OTHER_INCOME->value, $currentStart, $currentEnd, false);
        $otherIncomePrev = $this->getCategoryTotal($companyId, AccountCategoryEnum::OTHER_INCOME->value, $prevStart, $prevEnd, false);
        $netProfitCurrent = $revenueCurrent + $otherIncomeCurrent - $expenseCurrent;
        $netProfitPrev = $revenuePrev + $otherIncomePrev - $expensePrev;

        // Cash balance
        $cashBalance = $this->calculateCategoryBalanceAsOf($companyId, AccountCategoryEnum::CASH_BANK->value, $currentEnd, true);
        $cashBalancePrev = $this->calculateCategoryBalanceAsOf($companyId, AccountCategoryEnum::CASH_BANK->value, $prevEnd, true);

        // Sparkline: monthly revenue for last 12 months
        $revenueSparkline = $this->getMonthlyCategoryTotals($companyId, AccountCategoryEnum::REVENUE->value, 12, false);
        $expenseSparkline = $this->getMonthlyCategoryTotals($companyId, AccountCategoryEnum::COST_OF_GOODS_SOLD->value, 12, true);
        $profitSparkline = array_map(fn($r, $e) => max(0, $r - $e), $revenueSparkline, $expenseSparkline);
        $cashSparkline = $this->getMonthlyCashBalances($companyId, 12);

        return [
            'pendapatan' => [
                'value'    => $revenueCurrent,
                'delta'    => $revenuePrev > 0 ? round(($revenueCurrent - $revenuePrev) / $revenuePrev * 100, 1) : 0,
                'sparkline'=> $revenueSparkline,
            ],
            'pengeluaran' => [
                'value'    => $expenseCurrent,
                'delta'    => $expensePrev > 0 ? round(($expenseCurrent - $expensePrev) / $expensePrev * 100, 1) : 0,
                'sparkline'=> $expenseSparkline,
            ],
            'laba' => [
                'value'    => $netProfitCurrent,
                'delta'    => $netProfitPrev != 0 ? round(($netProfitCurrent - $netProfitPrev) / abs($netProfitPrev) * 100, 1) : 0,
                'sparkline'=> $profitSparkline,
            ],
            'kas' => [
                'value'    => $cashBalance,
                'delta'    => $cashBalancePrev != 0 ? round(($cashBalance - $cashBalancePrev) / abs($cashBalancePrev) * 100, 1) : 0,
                'sparkline'=> $cashSparkline,
            ],
        ];
    }

    /**
     * Monthly chart: sales vs purchases for last 12 months
     */
    public function getMonthlyChart(int $companyId): array
    {
        $months = [];
        $now = now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $start = $date->copy()->startOfMonth()->format('Y-m-d');
            $end = $date->copy()->endOfMonth()->format('Y-m-d');
            $label = $date->format('M');

            $sales = SalesInvoice::where('company_id', $companyId)
                ->whereNotIn('status', [SalesInvoiceStatus::DRAFT->value, SalesInvoiceStatus::CANCELLED->value])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total_amount');

            $purchases = PurchaseInvoice::where('company_id', $companyId)
                ->whereNotIn('status', [PurchaseInvoiceStatus::DRAFT->value, PurchaseInvoiceStatus::CANCELLED->value])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total_amount');

            // Convert to millions for chart display
            $months[] = [$label, round($sales / 1_000_000), round($purchases / 1_000_000)];
        }

        return $months;
    }

    /**
     * Sales pipeline: SO → Delivery → Invoice → Paid
     */
    public function getPipeline(int $companyId): array
    {
        $soOpen = SalesOrder::where('company_id', $companyId)
            ->where('status', SalesOrderStatus::OPEN->value)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->first();

        $deliveries = DeliveryOrder::where('company_id', $companyId)
            ->where('status', DeliveryOrderStatus::DRAFT->value)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->first();

        $invoicesOpen = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', [SalesInvoiceStatus::OPEN->value, SalesInvoiceStatus::PARTIAL->value])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(remaining_amount), 0) as total')
            ->first();

        $invoicesPaid = SalesInvoice::where('company_id', $companyId)
            ->where('status', SalesInvoiceStatus::PAID->value)
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->first();

        return [
            ['stage' => 'Sales Order',    'count' => (int) $soOpen->count,       'value' => (float) $soOpen->total,       'color' => 'oklch(0.85 0.06 60)'],
            ['stage' => 'Pengiriman',     'count' => (int) $deliveries->count,   'value' => (float) $deliveries->total,   'color' => 'oklch(0.78 0.10 50)'],
            ['stage' => 'Tagihan Terbit', 'count' => (int) $invoicesOpen->count, 'value' => (float) $invoicesOpen->total, 'color' => 'oklch(0.70 0.14 42)'],
            ['stage' => 'Lunas',          'count' => (int) $invoicesPaid->count, 'value' => (float) $invoicesPaid->total, 'color' => 'oklch(0.62 0.18 38)'],
        ];
    }

    /**
     * Recent activities: last 10 transactions across all types
     */
    public function getRecentActivities(int $companyId): array
    {
        $activities = [];

        // Recent Sales Orders
        $soList = SalesOrder::where('company_id', $companyId)
            ->whereNotIn('status', [SalesOrderStatus::CANCELLED->value])
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get(['id', 'number', 'order_date', 'total_amount', 'status', 'customer_id'])
            ->map(fn($so) => [
                'type'    => 'SO',
                'label'   => 'Sales Order',
                'number'  => $so->number,
                'date'    => $so->order_date,
                'amount'  => $so->total_amount,
                'status'  => $so->status,
                'url'     => route('penjualan.show', $so->id),
                'icon'    => 'cart',
                'color'   => 'var(--accent)',
            ]);

        // Recent Purchase Orders
        $poList = PurchaseOrder::where('company_id', $companyId)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get(['id', 'number', 'order_date', 'total_amount', 'status'])
            ->map(fn($po) => [
                'type'    => 'PO',
                'label'   => 'Purchase Order',
                'number'  => $po->number,
                'date'    => $po->order_date,
                'amount'  => $po->total_amount,
                'status'  => $po->status,
                'url'     => route('pembelian.show', $po->id),
                'icon'    => 'inbox',
                'color'   => 'var(--ink-2)',
            ]);

        // Recent Sales Invoices
        $siList = SalesInvoice::where('company_id', $companyId)
            ->whereNotIn('status', [SalesInvoiceStatus::DRAFT->value, SalesInvoiceStatus::CANCELLED->value])
            ->orderBy('invoice_date', 'desc')
            ->limit(5)
            ->get(['id', 'number', 'invoice_date', 'total_amount', 'status'])
            ->map(fn($si) => [
                'type'    => 'SI',
                'label'   => 'Tagihan Penjualan',
                'number'  => $si->number,
                'date'    => $si->invoice_date,
                'amount'  => $si->total_amount,
                'status'  => $si->status,
                'url'     => route('penjualan.tagihan_show', $si->id),
                'icon'    => 'receipt',
                'color'   => 'var(--good)',
            ]);

        // Recent Expenses
        $expList = Expense::where('company_id', $companyId)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get(['id', 'number', 'expense_date', 'total_amount', 'status'])
            ->map(fn($e) => [
                'type'    => 'EXP',
                'label'   => 'Biaya',
                'number'  => $e->number,
                'date'    => $e->expense_date,
                'amount'  => $e->total_amount,
                'status'  => $e->status,
                'url'     => route('biaya.show', $e->id),
                'icon'    => 'wallet',
                'color'   => 'var(--bad)',
            ]);

        $activities = collect()
            ->merge($soList)
            ->merge($poList)
            ->merge($siList)
            ->merge($expList)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->toArray();

        return $activities;
    }

    /**
     * Overdue invoices: sales + purchase invoices past due with remaining balance
     */
    public function getOverdueInvoices(int $companyId): array
    {
        $today = now()->format('Y-m-d');

        $salesOverdue = SalesInvoice::where('sales_invoices.company_id', $companyId)
            ->where('sales_invoices.due_date', '<', $today)
            ->where('sales_invoices.remaining_amount', '>', 0)
            ->whereNotIn('sales_invoices.status', [SalesInvoiceStatus::DRAFT->value, SalesInvoiceStatus::CANCELLED->value, SalesInvoiceStatus::PAID->value])
            ->join('contacts', 'contacts.id', '=', 'sales_invoices.customer_id')
            ->selectRaw('sales_invoices.id, sales_invoices.number, sales_invoices.due_date, sales_invoices.remaining_amount, contacts.name as contact_name, DATEDIFF(?, sales_invoices.due_date) as days_overdue', [$today])
            ->orderBy('sales_invoices.due_date')
            ->limit(5)
            ->get()
            ->map(fn($inv) => [
                'id'       => $inv->id,
                'number'   => $inv->number,
                'contact'  => $inv->contact_name,
                'due_date' => $inv->due_date,
                'amount'   => $inv->remaining_amount,
                'days'     => $inv->days_overdue,
                'type'     => 'Piutang',
                'url'      => route('penjualan.tagihan_show', $inv->id),
            ]);

        $purchaseOverdue = PurchaseInvoice::where('purchase_invoices.company_id', $companyId)
            ->where('purchase_invoices.due_date', '<', $today)
            ->where('purchase_invoices.remaining_amount', '>', 0)
            ->whereNotIn('purchase_invoices.status', [PurchaseInvoiceStatus::DRAFT->value, PurchaseInvoiceStatus::CANCELLED->value, PurchaseInvoiceStatus::PAID->value])
            ->join('contacts', 'contacts.id', '=', 'purchase_invoices.supplier_id')
            ->selectRaw('purchase_invoices.id, purchase_invoices.number, purchase_invoices.due_date, purchase_invoices.remaining_amount, contacts.name as contact_name, DATEDIFF(?, purchase_invoices.due_date) as days_overdue', [$today])
            ->orderBy('purchase_invoices.due_date')
            ->limit(5)
            ->get()
            ->map(fn($inv) => [
                'id'       => $inv->id,
                'number'   => $inv->number,
                'contact'  => $inv->contact_name,
                'due_date' => $inv->due_date,
                'amount'   => $inv->remaining_amount,
                'days'     => $inv->days_overdue,
                'type'     => 'Utang',
                'url'      => route('pembelian.tagihan_show', $inv->id),
            ]);

        return collect()
            ->merge($salesOverdue)
            ->merge($purchaseOverdue)
            ->sortBy('due_date')
            ->take(8)
            ->values()
            ->toArray();
    }

    /**
     * Top contacts: top 5 customers and vendors by invoice total
     */
    public function getTopContacts(int $companyId): array
    {
        $topCustomers = SalesInvoice::where('sales_invoices.company_id', $companyId)
            ->whereNotIn('sales_invoices.status', [SalesInvoiceStatus::DRAFT->value, SalesInvoiceStatus::CANCELLED->value])
            ->join('contacts', 'contacts.id', '=', 'sales_invoices.customer_id')
            ->selectRaw('contacts.id, contacts.name, SUM(sales_invoices.total_amount) as total, COUNT(*) as count')
            ->groupBy('contacts.id', 'contacts.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'name'  => $c->name,
                'total' => (float) $c->total,
                'count' => (int) $c->count,
                'type'  => 'Customer',
            ]);

        $topVendors = PurchaseInvoice::where('purchase_invoices.company_id', $companyId)
            ->whereNotIn('purchase_invoices.status', [PurchaseInvoiceStatus::DRAFT->value, PurchaseInvoiceStatus::CANCELLED->value])
            ->join('contacts', 'contacts.id', '=', 'purchase_invoices.supplier_id')
            ->selectRaw('contacts.id, contacts.name, SUM(purchase_invoices.total_amount) as total, COUNT(*) as count')
            ->groupBy('contacts.id', 'contacts.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'name'  => $c->name,
                'total' => (float) $c->total,
                'count' => (int) $c->count,
                'type'  => 'Vendor',
            ]);

        return [
            'customers' => $topCustomers->toArray(),
            'vendors'   => $topVendors->toArray(),
        ];
    }

    /**
     * Greeting stats: pending deliveries + overdue invoice count
     */
    public function getGreetingStats(int $companyId): array
    {
        $pendingDeliveries = DeliveryOrder::where('company_id', $companyId)
            ->where('status', DeliveryOrderStatus::DRAFT->value)
            ->count();

        $overdueInvoices = SalesInvoice::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->where('remaining_amount', '>', 0)
            ->whereNotIn('status', [SalesInvoiceStatus::DRAFT->value, SalesInvoiceStatus::CANCELLED->value, SalesInvoiceStatus::PAID->value])
            ->count();

        return [
            'pending_deliveries' => $pendingDeliveries,
            'overdue_invoices'   => $overdueInvoices,
        ];
    }

    // ============ Private Helpers ============

    /**
     * Get category total (debit or credit) for a date range
     */
    private function getCategoryTotal(int $companyId, int $categoryId, string $startDate, string $endDate, bool $isDebitNormal): float
    {
        $totals = JournalEntryItem::query()
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_items.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.category_id', $categoryId)
            ->where('journal_entries.company_id', $companyId)
            ->where('journal_entries.status', JournalEntryStatusEnum::POSTED->value)
            ->whereDate('journal_entries.journal_date', '>=', $startDate)
            ->whereDate('journal_entries.journal_date', '<=', $endDate)
            ->selectRaw('SUM(journal_entry_items.debit) as debit, SUM(journal_entry_items.credit) as credit')
            ->first();

        $debit = (float) ($totals->debit ?? 0);
        $credit = (float) ($totals->credit ?? 0);

        return $isDebitNormal ? $debit - $credit : $credit - $debit;
    }

    /**
     * Calculate category balance as of a date
     */
    private function calculateCategoryBalanceAsOf(int $companyId, int $categoryId, string $asOfDate, bool $isDebitNormal): float
    {
        $totals = JournalEntryItem::query()
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_items.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.category_id', $categoryId)
            ->where('journal_entries.company_id', $companyId)
            ->where('journal_entries.status', JournalEntryStatusEnum::POSTED->value)
            ->whereDate('journal_entries.journal_date', '<=', $asOfDate)
            ->selectRaw('SUM(journal_entry_items.debit) as debit, SUM(journal_entry_items.credit) as credit')
            ->first();

        $debit = (float) ($totals->debit ?? 0);
        $credit = (float) ($totals->credit ?? 0);

        return $isDebitNormal ? $debit - $credit : $credit - $debit;
    }

    /**
     * Get monthly category totals for sparkline (last N months)
     */
    private function getMonthlyCategoryTotals(int $companyId, int $categoryId, int $months, bool $isDebitNormal): array
    {
        $result = [];
        $now = now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $start = $date->copy()->startOfMonth()->format('Y-m-d');
            $end = $date->copy()->endOfMonth()->format('Y-m-d');
            $result[] = max(0, round($this->getCategoryTotal($companyId, $categoryId, $start, $end, $isDebitNormal) / 1_000_000));
        }

        return $result;
    }

    /**
     * Get monthly cash balances for sparkline
     */
    private function getMonthlyCashBalances(int $companyId, int $months): array
    {
        $result = [];
        $now = now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i)->endOfMonth();
            $result[] = max(0, round($this->calculateCategoryBalanceAsOf($companyId, AccountCategoryEnum::CASH_BANK->value, $date->format('Y-m-d'), true) / 1_000_000));
        }

        return $result;
    }
}
