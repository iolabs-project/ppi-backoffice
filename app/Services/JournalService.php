<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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

    public function fetchJournalTableData(Request $request)
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
}
