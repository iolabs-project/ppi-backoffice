<?php

namespace App\Services;

use App\Enums\AccountCategoryEnum;
use App\Enums\JournalEntryStatusEnum;
use App\Enums\SalesInvoiceStatus;
use Illuminate\Support\Collection;

use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportService
{
    public function fetchJournalTableData(Request $request, int $companyID)
    {
        $query = JournalEntry::with(['items' => function ($q) {
                $q->with('account')
                ->orderBy('id', 'asc');
            }])
            ->where('company_id', $companyID)
            ->where('status', JournalEntryStatusEnum::POSTED->value);

        if ($request->filled('start_date')) {
            $query->whereDate('journal_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('journal_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('items.account', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $query = $query->orderBy('journal_date', 'desc')->orderBy('number', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function fetchGeneralLedgerData(Request $request)
    {
        $query = DB::table('journal_entry_items', 'jei')
            ->select(
                'je.journal_date',
                'je.number as journal_number',
                'coa.name as account_name',
                'coa.code as account_code',
                'jei.debit',
                'jei.credit',
                'jei.description as item_description',
            )
            ->join('journal_entries as je', 'jei.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'jei.account_id', '=', 'coa.id');

        if ($request->filled('account_id')) {
            $query->where('jei.account_id', $request->input('account_id'));
        }

        if ($request->filled('start_date')) {
            $query->where('je.journal_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('je.journal_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('je.number', 'like', "%{$search}%")
                    ->orWhere('coa.name', 'like', "%{$search}%")
                    ->orWhere('coa.code', 'like', "%{$search}%");
            });
        }

        $query = $query->orderBy('je.journal_date', 'desc')->orderBy('je.number', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function fetchProfitLossTableData(Request $request, int $companyID)
    {
        $sectionCategoryMap = [
            AccountCategoryEnum::REVENUE->value => 'revenue',
            AccountCategoryEnum::COST_OF_GOODS_SOLD->value => 'cogs',
            AccountCategoryEnum::OPERATING_EXPENSE->value => 'operating_expense',
            AccountCategoryEnum::OTHER_INCOME->value => 'other_income',
            AccountCategoryEnum::OTHER_EXPENSE->value => 'other_expense',
        ];

        $creditNormalSections = ['revenue', 'other_income'];

        $items = JournalEntryItem::with([
            'account:id,name,code,category_id',
            'journalEntry:id,number,journal_date,status,company_id',
        ])
            ->whereHas('account', function ($q) use ($sectionCategoryMap) {
                $q->whereIn('category_id', array_keys($sectionCategoryMap));
            })
            ->whereHas('journalEntry', function ($q) use ($companyID) {
                $q->where('company_id', $companyID)
                    ->where('status', JournalEntryStatusEnum::POSTED->value);
            });

        if ($request->filled('start_date')) {
            $items->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('journal_date', '>=', $request->input('start_date'));
            });
        }

        if ($request->filled('end_date')) {
            $items->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('journal_date', '<=', $request->input('end_date'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $items->where(function ($q) use ($search) {
                $q->whereHas('journalEntry', function ($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%");
                })
                    ->orWhereHas('account', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $sections = $items->get()
            ->groupBy(fn ($item) => $sectionCategoryMap[$item->account->category_id] ?? 'unknown')
            ->map(fn ($accountItems, $section) => $this->buildAccountSection(
                $accountItems,
                in_array($section, $creditNormalSections, true)
            ));

        $emptySection = ['accounts' => collect(), 'total' => 0];

        $revenue = $sections['revenue']['total'] ?? 0;
        $cogs = $sections['cogs']['total'] ?? 0;
        $operatingExpense = $sections['operating_expense']['total'] ?? 0;
        $otherIncome = $sections['other_income']['total'] ?? 0;
        $otherExpense = $sections['other_expense']['total'] ?? 0;

        $grossProfit = $revenue - $cogs;
        $operatingProfit = $grossProfit - $operatingExpense;
        $netProfit = $operatingProfit + $otherIncome - $otherExpense;

        return [
            'revenue' => $sections['revenue'] ?? $emptySection,
            'cogs' => $sections['cogs'] ?? $emptySection,
            'gross_profit' => $grossProfit,
            'operating_expense' => $sections['operating_expense'] ?? $emptySection,
            'operating_profit' => $operatingProfit,
            'other_income' => $sections['other_income'] ?? $emptySection,
            'other_expense' => $sections['other_expense'] ?? $emptySection,
            'net_profit' => $netProfit,
        ];
    }

    public function fetchBalanceSheetData(Request $request, int $companyID)
    {
        $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));

        $sectionCategoryMap = [
            AccountCategoryEnum::CASH_BANK->value => 'asset',
            AccountCategoryEnum::ACCOUNT_RECEIVABLE->value => 'asset',
            AccountCategoryEnum::INVENTORY->value => 'asset',
            AccountCategoryEnum::OTHER_CURRENT_ASSET->value => 'asset',
            AccountCategoryEnum::FIXED_ASSET->value => 'asset',
            AccountCategoryEnum::ACCUMULATED_DEPRECIATION_AMORTIZATION->value => 'asset',
            AccountCategoryEnum::OTHER_ASSET->value => 'asset',
            AccountCategoryEnum::ACCOUNT_PAYABLE->value => 'liability',
            AccountCategoryEnum::OTHER_CURRENT_LIABILITY->value => 'liability',
            AccountCategoryEnum::LONG_TERM_LIABILITY->value => 'liability',
            AccountCategoryEnum::CREDIT_CARD->value => 'liability',
            AccountCategoryEnum::EQUITY->value => 'equity',
        ];

        $debitNormalSections = ['asset'];

        $items = JournalEntryItem::with(['account:id,name,code,category_id'])
            ->whereHas('account', function ($q) use ($sectionCategoryMap) {
                $q->whereIn('category_id', array_keys($sectionCategoryMap));
            })
            ->whereHas('journalEntry', function ($q) use ($companyID, $asOfDate) {
                $q->where('company_id', $companyID)
                    ->where('status', JournalEntryStatusEnum::POSTED->value)
                    ->whereDate('journal_date', '<=', $asOfDate);
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $items->whereHas('account', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $sections = $items->get()
            ->groupBy(fn ($item) => $sectionCategoryMap[$item->account->category_id] ?? 'unknown')
            ->map(fn ($accountItems, $section) => $this->buildAccountSection(
                $accountItems,
                !in_array($section, $debitNormalSections, true)
            ));

        $emptySection = ['accounts' => collect(), 'total' => 0];

        $asset = $sections['asset'] ?? $emptySection;
        $liability = $sections['liability'] ?? $emptySection;
        $equity = $sections['equity'] ?? $emptySection;

        $currentEarnings = $this->calculateNetIncomeAsOf($companyID, $asOfDate);

        $equityAccounts = $equity['accounts']->push([
            'account_id' => null,
            'account_code' => null,
            'account_name' => 'Laba Tahun Berjalan',
            'debit' => 0,
            'credit' => 0,
            'balance' => $currentEarnings,
        ])->values();

        $equityTotal = $equity['total'] + $currentEarnings;

        return [
            'as_of_date' => $asOfDate,
            'asset' => $asset,
            'liability' => $liability,
            'equity' => [
                'accounts' => $equityAccounts,
                'total' => $equityTotal,
            ],
            'total_liabilities_and_equity' => $liability['total'] + $equityTotal,
        ];
    }

    public function fetchCashFlowData(Request $request, int $companyID)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $openingDate = Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $categoryTotals = $this->calculateCashFlowByCategory($companyID, $startDate, $endDate);

        $rowCategoryMap = [
            'operating' => [
                'receipts_from_customers' => [AccountCategoryEnum::REVENUE->value, AccountCategoryEnum::ACCOUNT_RECEIVABLE->value],
                'other_current_assets' => [AccountCategoryEnum::INVENTORY->value, AccountCategoryEnum::OTHER_CURRENT_ASSET->value],
                'payments_to_suppliers' => [AccountCategoryEnum::COST_OF_GOODS_SOLD->value, AccountCategoryEnum::ACCOUNT_PAYABLE->value],
                'credit_card_and_other_current_liabilities' => [AccountCategoryEnum::OTHER_CURRENT_LIABILITY->value],
                'other_income' => [AccountCategoryEnum::OTHER_INCOME->value],
                'operating_expense_payments' => [AccountCategoryEnum::OPERATING_EXPENSE->value, AccountCategoryEnum::OTHER_EXPENSE->value],
                'credit_card_payments' => [AccountCategoryEnum::CREDIT_CARD->value],
            ],
            'investing' => [
                'asset_acquisition' => [AccountCategoryEnum::FIXED_ASSET->value, AccountCategoryEnum::ACCUMULATED_DEPRECIATION_AMORTIZATION->value],
                'other_investing' => [AccountCategoryEnum::OTHER_ASSET->value],
            ],
            'financing' => [
                'long_term_liabilities' => [AccountCategoryEnum::LONG_TERM_LIABILITY->value],
                'owner_equity' => [AccountCategoryEnum::EQUITY->value],
            ],
        ];

        $activities = [];

        foreach ($rowCategoryMap as $activity => $rows) {
            $lines = [];

            foreach ($rows as $rowKey => $categoryIds) {
                $lines[$rowKey] = array_sum(array_map(
                    fn ($categoryId) => $categoryTotals[$categoryId] ?? 0,
                    $categoryIds
                ));
            }

            $activities[$activity] = [
                'lines' => $lines,
                'total' => array_sum($lines),
            ];
        }

        $netCashFlow = $activities['operating']['total'] + $activities['investing']['total'] + $activities['financing']['total'];
        $openingCashBalance = $this->calculateCategoryBalanceAsOf($companyID, AccountCategoryEnum::CASH_BANK->value, $openingDate, true);
        $closingCashBalance = $openingCashBalance + $netCashFlow;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'operating' => $activities['operating'],
            'investing' => $activities['investing'],
            'financing' => $activities['financing'],
            'net_cash_flow' => $netCashFlow,
            'opening_cash_balance' => $openingCashBalance,
            'closing_cash_balance' => $closingCashBalance,
        ];
    }

    private function calculateCashFlowByCategory(int $companyID, string $startDate, string $endDate): array
    {
        $entries = JournalEntry::with(['items.account:id,category_id'])
            ->where('company_id', $companyID)
            ->where('status', JournalEntryStatusEnum::POSTED->value)
            ->whereDate('journal_date', '>=', $startDate)
            ->whereDate('journal_date', '<=', $endDate)
            ->whereHas('items.account', function ($q) {
                $q->where('category_id', AccountCategoryEnum::CASH_BANK->value);
            })
            ->get();

        $categoryTotals = [];

        foreach ($entries as $entry) {
            $cashItems = $entry->items->filter(
                fn ($item) => $item->account->category_id === AccountCategoryEnum::CASH_BANK->value
            );
            $nonCashItems = $entry->items->filter(
                fn ($item) => $item->account->category_id !== AccountCategoryEnum::CASH_BANK->value
            );

            $cashNet = $cashItems->sum('debit') - $cashItems->sum('credit');
            $weightTotal = $nonCashItems->sum(fn ($item) => $item->debit + $item->credit);

            if ($cashNet == 0 || $weightTotal <= 0) {
                continue;
            }

            foreach ($nonCashItems->groupBy(fn ($item) => $item->account->category_id) as $categoryId => $items) {
                $weight = $items->sum(fn ($item) => $item->debit + $item->credit) / $weightTotal;
                $categoryTotals[$categoryId] = ($categoryTotals[$categoryId] ?? 0) + ($cashNet * $weight);
            }
        }

        return $categoryTotals;
    }

    public function fetchExecutiveSummaryData(Request $request, int $companyID)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $periodDays = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);
        $periodStartMinusOne = Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $cashTotals = $this->calculateCashFlowTotals($companyID, $startDate, $endDate);
        $closingCashBalance = $this->calculateCategoryBalanceAsOf($companyID, AccountCategoryEnum::CASH_BANK->value, $endDate, true);

        $profitLossRequest = Request::create('', 'GET', ['start_date' => $startDate, 'end_date' => $endDate]);
        $profitLoss = $this->fetchProfitLossTableData($profitLossRequest, $companyID);

        $revenue = $profitLoss['revenue']['total'];
        $cogs = $profitLoss['cogs']['total'];
        $grossProfit = $profitLoss['gross_profit'];
        $netProfit = $profitLoss['net_profit'];
        $expenses = $grossProfit - $netProfit;

        $balanceSheetRequest = Request::create('', 'GET', ['as_of_date' => $endDate]);
        $balanceSheet = $this->fetchBalanceSheetData($balanceSheetRequest, $companyID);

        $totalAsset = $balanceSheet['asset']['total'];
        $totalLiability = $balanceSheet['liability']['total'];
        $totalEquity = $balanceSheet['equity']['total'];

        $invoiceStats = SalesInvoice::query()
            ->where('company_id', $companyID)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereNotIn('status', [SalesInvoiceStatus::DRAFT->value, SalesInvoiceStatus::CANCELLED->value])
            ->selectRaw('COUNT(*) as invoice_count, COALESCE(AVG(total_amount), 0) as average_invoice')
            ->first();

        $averageReceivable = (
            $this->calculateCategoryBalanceAsOf($companyID, AccountCategoryEnum::ACCOUNT_RECEIVABLE->value, $periodStartMinusOne, true)
            + $this->calculateCategoryBalanceAsOf($companyID, AccountCategoryEnum::ACCOUNT_RECEIVABLE->value, $endDate, true)
        ) / 2;

        $averagePayable = (
            $this->calculateCategoryBalanceAsOf($companyID, AccountCategoryEnum::ACCOUNT_PAYABLE->value, $periodStartMinusOne, false)
            + $this->calculateCategoryBalanceAsOf($companyID, AccountCategoryEnum::ACCOUNT_PAYABLE->value, $endDate, false)
        ) / 2;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'cash' => [
                'cash_in' => $cashTotals['cash_in'],
                'cash_out' => $cashTotals['cash_out'],
                'cash_change' => $cashTotals['cash_in'] - $cashTotals['cash_out'],
                'closing_balance' => $closingCashBalance,
            ],
            'profitability' => [
                'revenue' => $revenue,
                'cost_of_goods_sold' => $cogs,
                'gross_profit' => $grossProfit,
                'expenses' => $expenses,
                'net_profit' => $netProfit,
            ],
            'balance_sheet' => [
                'asset' => $totalAsset,
                'liability' => $totalLiability,
                'equity' => $totalEquity,
            ],
            'revenue_stats' => [
                'invoice_count' => (int) ($invoiceStats->invoice_count ?? 0),
                'average_invoice' => (float) ($invoiceStats->average_invoice ?? 0),
            ],
            'performance' => [
                'gross_profit_margin' => $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0,
                'net_profit_margin' => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0,
                'roi_annualized' => $totalAsset > 0 ? ($netProfit / $totalAsset) * (365 / $periodDays) * 100 : 0,
            ],
            'position' => [
                'receivable_days' => $revenue > 0 ? ($averageReceivable / $revenue) * $periodDays : 0,
                'payable_days' => $cogs > 0 ? ($averagePayable / $cogs) * $periodDays : 0,
                'debt_to_equity_ratio' => $totalEquity != 0 ? $totalLiability / $totalEquity : 0,
                'asset_to_liability_ratio' => $totalLiability != 0 ? $totalAsset / $totalLiability : 0,
            ],
        ];
    }

    public function fetchReceivableReportData(Request $request, int $companyID)
    {
        return $this->fetchOutstandingInvoices(SalesInvoice::class, 'customer_id', $request, $companyID);
    }

    public function fetchPayableReportData(Request $request, int $companyID)
    {
        return $this->fetchOutstandingInvoices(PurchaseInvoice::class, 'supplier_id', $request, $companyID);
    }

    private function fetchOutstandingInvoices(string $modelClass, string $contactForeignKey, Request $request, int $companyID): array
    {
        $table = (new $modelClass())->getTable();

        $query = $modelClass::query()
            ->join('contacts', 'contacts.id', '=', "{$table}.{$contactForeignKey}")
            ->where("{$table}.company_id", $companyID)
            ->where("{$table}.remaining_amount", '>', 0)
            ->whereNotIn("{$table}.status", ['draft', 'cancelled'])
            ->select(
                "{$table}.id",
                "{$table}.number",
                "{$table}.due_date",
                "{$table}.remaining_amount",
                'contacts.name as contact_name',
            );

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search, $table) {
                $q->where("{$table}.number", 'like', "%{$search}%")
                    ->orWhere('contacts.name', 'like', "%{$search}%");
            });
        }

        $today = now()->startOfDay();

        $invoices = $query->orderBy("{$table}.due_date")
            ->get()
            ->map(function ($invoice) use ($today) {
                $dueDate = Carbon::parse($invoice->due_date)->startOfDay();

                return [
                    'id' => $invoice->id,
                    'contact_name' => $invoice->contact_name,
                    'number' => $invoice->number,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'amount' => (float) $invoice->remaining_amount,
                    'days_overdue' => $dueDate->diffInDays($today, false),
                ];
            })
            ->values();

        return [
            'invoices' => $invoices,
            'total' => $invoices->sum('amount'),
        ];
    }

    private function calculateCashFlowTotals(int $companyID, string $startDate, string $endDate): array
    {
        $totals = JournalEntryItem::query()
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_items.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.category_id', AccountCategoryEnum::CASH_BANK->value)
            ->where('journal_entries.company_id', $companyID)
            ->where('journal_entries.status', JournalEntryStatusEnum::POSTED->value)
            ->whereDate('journal_entries.journal_date', '>=', $startDate)
            ->whereDate('journal_entries.journal_date', '<=', $endDate)
            ->selectRaw('SUM(journal_entry_items.debit) as cash_in, SUM(journal_entry_items.credit) as cash_out')
            ->first();

        return [
            'cash_in' => (float) ($totals->cash_in ?? 0),
            'cash_out' => (float) ($totals->cash_out ?? 0),
        ];
    }

    private function calculateCategoryBalanceAsOf(int $companyID, int $categoryId, string $asOfDate, bool $isDebitNormal): float
    {
        $totals = JournalEntryItem::query()
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_items.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.category_id', $categoryId)
            ->where('journal_entries.company_id', $companyID)
            ->where('journal_entries.status', JournalEntryStatusEnum::POSTED->value)
            ->whereDate('journal_entries.journal_date', '<=', $asOfDate)
            ->selectRaw('SUM(journal_entry_items.debit) as debit, SUM(journal_entry_items.credit) as credit')
            ->first();

        $debit = (float) ($totals->debit ?? 0);
        $credit = (float) ($totals->credit ?? 0);

        return $isDebitNormal ? $debit - $credit : $credit - $debit;
    }

    private function buildAccountSection(Collection $items, bool $isCreditNormal): array
    {
        $accounts = $items
            ->groupBy('account_id')
            ->map(function ($accountItems) use ($isCreditNormal) {
                $account = $accountItems->first()->account;
                $debit = $accountItems->sum('debit');
                $credit = $accountItems->sum('credit');

                return [
                    'account_id' => $account->id,
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $isCreditNormal ? $credit - $debit : $debit - $credit,
                ];
            })
            ->values();

        return [
            'accounts' => $accounts,
            'total' => $accounts->sum('balance'),
        ];
    }

    private function calculateNetIncomeAsOf(int $companyID, string $asOfDate): float
    {
        $profitLossRequest = Request::create('', 'GET', ['end_date' => $asOfDate]);

        return $this->fetchProfitLossTableData($profitLossRequest, $companyID)['net_profit'];
    }
}
