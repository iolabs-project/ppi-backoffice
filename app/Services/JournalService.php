<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psy\Clipboard\NullClipboardMethod;

class JournalService
{
    public function post(string | null $date, string | null $referenceType, int | null $referenceId, string | null $description, array $items)
    {
        DB::transaction(function () use ($date, $referenceType, $referenceId, $description, $items) {
            $journalEntry = JournalEntry::create([
                'company_id' => config('context.selected_company_id'),
                'number' => $this->generateJournalNumber(),
                'journal_date' => $date ?? now(),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'status' => 'posted',
                'created_by' => auth()->id(),
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
            throw new \Exception('Journal entry is already cancelled.');
        }

        if ($journalEntry->status === 'posted') {
            throw new \Exception('Cannot cancel a posted journal entry.');
        }

        $journalEntry->update(['status' => 'cancelled']);
    }

    public function reverse(int $journalEntryId, string $date, string | null $description)
    {
        $originalEntry = JournalEntry::with('items')->findOrFail($journalEntryId);

        if ($originalEntry->status === 'cancelled') {
            throw new \Exception('Cannot reverse a cancelled journal entry.');
        }

        DB::transaction(function () use ($originalEntry, $date, $description) {
            $reversalEntry = JournalEntry::create([
                'company_id' => config('context.selected_company_id'),
                'number' => $this->generateJournalNumber(),
                'journal_date' => $date,
                'reference_type' => get_class($originalEntry),
                'reference_id' => $originalEntry->id,
                'description' => $description ?? "Reversal of Journal Entry #{$originalEntry->number}",
                'status' => 'posted',
                'created_by' => auth()->id(),
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
}
