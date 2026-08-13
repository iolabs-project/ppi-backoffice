<?php

namespace App\Services;

use App\Enums\AccountCategoryEnum;
use App\Enums\JournalEntryStatusEnum;
use Illuminate\Validation\ValidationException;

use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class JournalService
{
    public function post(string | null $date, string | null $referenceType, int | null $referenceID, string | null $description, array $items)
    {
        DB::transaction(function () use ($date, $referenceType, $referenceID, $description, $items) {
            $journalEntry = JournalEntry::create([
                'company_id' => config('context.selected_company_id'),
                'number' => $this->generateJournalNumber(),
                'journal_date' => $date ?? now(),
                'reference_type' => $referenceType,
                'reference_id' => $referenceID,
                'description' => $description,
                'status' => 'posted',
                'created_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'description' => $item['description'] ?? null,
                ]);
            }
        });
    }

    public function cancel(int $journalEntryId)
    {
        $journalEntry = JournalEntry::findOrFail($journalEntryId);

        if ($journalEntry->status === 'cancelled') {
            throw ValidationException::withMessages(['error' => 'Jurnal sudah dibatalkan.']);
        }

        if ($journalEntry->status === 'posted') {
            throw ValidationException::withMessages(['error' => 'Tidak dapat membatalkan jurnal yang sudah diposting.']);
        }

        $journalEntry->update(['status' => 'cancelled']);
    }

    public function reverse(int $journalEntryID, string | null $date, string | null $description)
    {
        $originalEntry = JournalEntry::with('items')->findOrFail($journalEntryID);

        if ($originalEntry->status === 'cancelled') {
            throw ValidationException::withMessages(['error' => 'Tidak dapat membalikkan jurnal yang sudah dibatalkan.']);
        }

        DB::transaction(function () use ($originalEntry, $date, $description) {
            $reversalEntry = JournalEntry::create([
                'company_id' => config('context.selected_company_id'),
                'number' => $this->generateJournalNumber(),
                'journal_date' => $date ?? now(),
                'reference_type' => get_class($originalEntry),
                'reference_id' => $originalEntry->id,
                'description' => $description ?? "Reversal of Journal Entry #{$originalEntry->number}",
                'status' => 'posted',
                'created_by' => Auth::id(),
            ]);

            foreach ($originalEntry->items as $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $item->account_id,
                    'debit' => $item->credit,
                    'credit' => $item->debit,
                    'description' => "Reversal of item from Journal Entry #{$originalEntry->number}",
                ]);
            }
        });
    }

    public function generateJournalNumber(): string
    {
        $prefix = 'JNL';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = JournalEntry::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchJournalTableData(Request $request, int $companyID)
    {
        $query = JournalEntry::with(['items.account'])
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

    public function fetchPofitLossTableData(Request $request)
    {
        $categories = [
            AccountCategoryEnum::REVENUE->value,
            AccountCategoryEnum::COST_OF_GOODS_SOLD->value,
            AccountCategoryEnum::OPERATING_EXPENSE->value,
            AccountCategoryEnum::OTHER_INCOME->value,
            AccountCategoryEnum::OTHER_EXPENSE->value,
        ];
        $query = JournalEntryItem::with([
            'account:id,name,code,category_id',
            'journalEntry:id,number,journal_date,status'
        ])
            ->whereHas('account', function ($q) use ($categories) {
                $q->whereIn('category_id', $categories);
            });

        if ($request->filled('start_date')) {
            $query->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('journal_date', '>=', $request->input('start_date'));
            });
        }

        if ($request->filled('end_date')) {
            $query->whereHas('journalEntry', function ($q) use ($request) {
                $q->whereDate('journal_date', '<=', $request->input('end_date'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('journalEntry', function ($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%");
                })
                    ->orWhereHas('account', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $query = $query->get();

        $sections = $query
            ->groupBy(function ($item) use ($categories) {
                return $categories[$item->account->category_id];
            })
            ->map(function ($items, $category) {
                $accounts = $items
                    ->groupBy('account_id')
                    ->map(function ($accountItems) use ($category) {
                        $account = $accountItems->first()->account;

                        $debit = $accountItems->sum('debit');
                        $credit = $accountItems->sum('credit');

                        $balance = in_array($category, [
                            'revenue',
                            'other_income',
                        ])
                            ? $credit - $debit
                            : $debit - $credit;

                        return [
                            'account_id' => $account->id,
                            'account_code' => $account->code,
                            'account_name' => $account->name,
                            'debit' => $debit,
                            'credit' => $credit,
                            'balance' => $balance,
                        ];
                    })
                    ->values();

                return [
                    'accounts' => $accounts,
                    'total' => $accounts->sum('balance'),
                ];
            });

        $revenue = $sections['revenue']['total'] ?? 0;
        $cogs = $sections['cogs']['total'] ?? 0;
        $operatingExpense = $sections['operating_expense']['total'] ?? 0;
        $otherIncome = $sections['other_income']['total'] ?? 0;
        $otherExpense = $sections['other_expense']['total'] ?? 0;

        $grossProfit = $revenue - $cogs;

        $operatingProfit = $grossProfit - $operatingExpense;

        $netProfit =
            $operatingProfit
            + $otherIncome
            - $otherExpense;

        $result = [
            'revenue' => $sections['revenue'] ?? [
                'accounts' => collect(),
                'total' => 0,
            ],

            'cogs' => $sections['cogs'] ?? [
                'accounts' => collect(),
                'total' => 0,
            ],

            'gross_profit' => $grossProfit,

            'operating_expense' => $sections['operating_expense'] ?? [
                'accounts' => collect(),
                'total' => 0,
            ],

            'operating_profit' => $operatingProfit,

            'other_income' => $sections['other_income'] ?? [
                'accounts' => collect(),
                'total' => 0,
            ],

            'other_expense' => $sections['other_expense'] ?? [
                'accounts' => collect(),
                'total' => 0,
            ],

            'net_profit' => $netProfit,
        ];

        return $result;
    }
}
