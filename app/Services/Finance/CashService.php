<?php

namespace App\Services\Finance;

use App\Enums\AccountCategoryEnum;
use App\Enums\JournalEntryStatusEnum;
use App\Models\ChartOfAccount;
use App\Models\CashTransaction;
use App\Models\CashTransactionCost;
use App\Enums\AccountSettingEnum;
use App\Enums\CashTransactionStatusEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Models\CashTransactionItem;
use App\Services\JournalService;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function generateNumber(int $companyID)
    {
        $prefix = 'CASH';
        $companyCode = Company::select('code')->where('id', $companyID)->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = CashTransaction::whereYear('created_at', date('Y'))
            ->where('company_id', $companyID)
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchActiveAccountData(int $companyID, bool $includeBalance = true, array $excludeAccountIDs = [])
    {
        $accountQuery = ChartOfAccount::select('id', 'code', 'name')
            ->where('company_id', $companyID)
            ->where('category_id', AccountCategoryEnum::CASH_BANK->value)
            ->whereNull('deleted_at')
            ->when(!empty($excludeAccountIDs), function ($query) use ($excludeAccountIDs) {
                $query->whereNotIn('id', $excludeAccountIDs);
            })
            ->get();

        if (!$includeBalance) {
            return $accountQuery;
        }

        $balanceQuery = DB::table('journal_entry_items', 'jei')
            ->join('journal_entries as je', 'je.id', '=', 'jei.journal_entry_id')
            ->select('jei.account_id', DB::raw('SUM(jei.debit) - SUM(jei.credit) as balance'))
            ->where('je.company_id', $companyID)
            ->where('je.status', JournalEntryStatusEnum::POSTED->value)
            ->groupBy('jei.account_id')
            ->get();


        return $accountQuery->map(function ($account) use ($balanceQuery) {
            $balance = $balanceQuery->firstWhere('account_id', $account->id);
            $account->balance = $balance->balance ?? 0;
            return $account;
        });
    }

    public function fetchActiveAccountDataByID(int $companyID, int $accountID)
    {
        $accountQuery = ChartOfAccount::select('id', 'code', 'name')
            ->where('company_id', $companyID)
            ->where('category_id', AccountCategoryEnum::CASH_BANK->value)
            ->where('id', $accountID)
            ->whereNull('deleted_at')
            ->first();

        $balanceQuery = DB::table('journal_entry_items', 'jei')
            ->join('journal_entries as je', 'je.id', '=', 'jei.journal_entry_id')
            ->select('jei.account_id', DB::raw('SUM(jei.debit) - SUM(jei.credit) as balance'))
            ->where('je.company_id', $companyID)
            ->where('jei.account_id', $accountID)
            ->where('je.status', JournalEntryStatusEnum::POSTED->value)
            ->groupBy('jei.account_id')
            ->get();


        $balance = $balanceQuery->firstWhere('account_id', $accountQuery->id);
        $accountQuery->balance = $balance->balance ?? 0;
        return $accountQuery;
    }

    public function fetchTransactionByID(int $id)
    {
        return CashTransaction::with(['items.account', 'costs.account', 'toAccount', 'fromAccount', 'contact', 'creator'])->findOrFail($id);
    }

    public function fetchTransactionTableData(Request $request)
    {
        $query = CashTransaction::with(['toAccount', 'fromAccount', 'contact', 'reference', 'creator'])
            ->select('id', 'from_account_id', 'to_account_id', 'contact_id', 'reference_id', 'reference_type', 'number', 'transaction_date', 'type', 'status', 'description', 'total_amount', 'created_by');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('salesOrder', function ($q) use ($search) {
                        $q->where('number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('account_id')) {
            $query->where('from_account_id', $request->input('account_id'))
                ->orWhere('to_account_id', $request->input('account_id'));
        }

        $query = $query->orderBy('transaction_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function storeTransaction(Request $request, int $companyID)
    {
        $itemsCollection = collect($request->input('items', []));
        $costCollection = collect($request->input('costs', []));
        DB::transaction(function () use ($request, $itemsCollection, $costCollection, $companyID) {
            // $subtotal = $itemsCollection->sum('amount');
            $subtotal = $itemsCollection->count() == 0 && $request->filled('subtotal') ? $request->input('subtotal') : $itemsCollection->sum('amount');
            $costTotalAmount = $costCollection->sum('amount');
            $taxPercentage = $request->input('tax_percentage') ?? 0;
            $taxAmount = $subtotal * ($taxPercentage / 100);
            $transaction = CashTransaction::create([
                'company_id' => $companyID,
                'to_account_id' => $request->input('to_account_id'),
                'from_account_id' => $request->input('from_account_id'),
                'contact_id' => $request->input('contact_id'),
                'reference_type' => $request->input('reference_type'),
                'reference_id' => $request->input('reference_id'),
                'number' => $this->generateNumber($companyID),
                'reference_number' => $request->input('reference_number'),
                'transaction_date' => $request->input('transaction_date'),
                'type' => $request->input('type'),
                'status' => $request->input('status', 'draft'),
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount + $costTotalAmount,
                'description' => $request->input('description'),
                'created_by' => Auth::id(),
            ]);



            CashTransactionItem::where('cash_transaction_id', $transaction->id)->delete();
            CashTransactionCost::where('cash_transaction_id', $transaction->id)->delete();

            foreach ($itemsCollection as $item) {
                CashTransactionItem::create([
                    'cash_transaction_id' => $transaction->id,
                    'account_id' => $item['account_id'],
                    'description' => $item['description'] ?? null,
                    'amount' => $item['amount'] ?? 0,
                ]);
            }

            foreach ($costCollection as $cost) {
                CashTransactionCost::create([
                    'cash_transaction_id' => $transaction->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'amount' => $cost['amount'] ?? 0,
                ]);
            }

            // Skip if have rererence type and reference id, because journal entry will be created in the reference model
            if ($transaction->status === CashTransactionStatusEnum::POSTED->value && !$transaction->reference_type && !$transaction->reference_id) {
                $this->postCashJournal($transaction);
            }
        });
    }

    public function updateTransaction(Request $request, int $accountID, int $transactionID)
    {
        $itemsCollection = collect($request->input('items', []));
        $costCollection = collect($request->input('costs', []));
        DB::transaction(function () use ($request, $itemsCollection, $costCollection, $transactionID, $accountID) {
            $transaction = CashTransaction::findOrFail($transactionID);
            $subtotal = $itemsCollection->count() == 0 && $request->filled('subtotal') ? $request->input('subtotal') : $itemsCollection->sum('amount');
            $costTotalAmount = $costCollection->sum('amount');
            $taxPercentage = $request->input('tax_percentage') ?? 0;
            $taxAmount = $subtotal * ($taxPercentage / 100);
            $transaction->update([
                'to_account_id' => $request->input('to_account_id'),
                'from_account_id' => $request->input('from_account_id'),
                'contact_id' => $request->input('contact_id'),
                'reference_type' => $request->input('reference_type'),
                'reference_id' => $request->input('reference_id'),
                'reference_number' => $request->input('reference_number'),
                'transaction_date' => $request->input('transaction_date'),
                'type' => $request->input('type'),
                'status' => $request->input('status', 'draft'),
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount + $costTotalAmount,
                'description' => $request->input('description'),
            ]);

            CashTransactionItem::where('cash_transaction_id', $transaction->id)->delete();
            CashTransactionCost::where('cash_transaction_id', $transaction->id)->delete();

            foreach ($itemsCollection as $item) {
                CashTransactionItem::create([
                    'cash_transaction_id' => $transaction->id,
                    'account_id' => $item['account_id'],
                    'description' => $item['description'] ?? null,
                    'amount' => $item['amount'] ?? 0,
                ]);
            }

            foreach ($costCollection as $cost) {
                CashTransactionCost::create([
                    'cash_transaction_id' => $transaction->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'amount' => $cost['amount'] ?? 0,
                ]);
            }

            // Skip if have rererence type and reference id, because journal entry will be created in the reference model
            if ($transaction->status === CashTransactionStatusEnum::POSTED->value && !$transaction->reference_type && !$transaction->reference_id) {
                $this->postCashJournal($transaction);
            }
        });
    }

    public function cancelTransaction(int $id, int $companyID)
    {
        DB::transaction(function () use ($id, $companyID) {
            $transaction = CashTransaction::where('id', $id)
                ->where('company_id', $companyID)
                ->firstOrFail();
            if ($transaction->status === CashTransactionStatusEnum::CANCELLED->value) {
                throw ValidationException::withMessages(['status' => 'Tidak dapat membatalkan transaksi kas yang sudah dibatalkan.']);
            }

            if ($transaction->status === CashTransactionStatusEnum::POSTED->value) {
                throw ValidationException::withMessages(['status' => 'Tidak dapat membatalkan transaksi kas yang sudah diposting.']);
            }
            $transaction->update([
                'status' => CashTransactionStatusEnum::CANCELLED->value,
            ]);
        });
    }

    private function postCashJournal(CashTransaction $transaction)
    {
        if ($transaction->type === CashTransactionTypeEnum::TRANSFER->value) {

            $journalItems = [
                [
                    'account_id' => $transaction->to_account_id,
                    'debit' => $transaction->total_amount,
                ],
                [
                    'account_id' => $transaction->from_account_id,
                    'credit' => $transaction->total_amount,
                ]
            ];

            $this->journalService->post(
                date: null,
                referenceType: CashTransaction::class,
                referenceID: $transaction->id,
                description: 'Transfer Dana #' . $transaction->number,
                items: $journalItems
            );
        } else if ($transaction->type === CashTransactionTypeEnum::RECEIVE->value) {
            $transaction->load('items');
            $debitAccountID = $transaction->to_account_id;
            $debitAmount = $transaction->subtotal + $transaction->tax_amount;

            $taxAccountID = AccountSetting::where('company_id', $transaction->company_id)
                ->where('setting_key', AccountSettingEnum::OUTPUT_TAX->value)
                ->value('account_id');
            $taxAmount = $transaction->tax_amount;

            // $journalItems = [
            //     [
            //         'account_id' => $debitAccountID,
            //         'debit' => $debitAmount,
            //     ],
            //     [
            //         'account_id' => $taxAccountID,
            //         'credit' => $taxAmount,
            //     ],
            // ];

            $journalItems[] = [
                'account_id' => $debitAccountID,
                'debit' => $debitAmount,
            ];

            if ($taxAmount > 0) {
                $journalItems[] = [
                    'account_id' => $taxAccountID,
                    'credit' => $taxAmount,
                ];
            }

            foreach ($transaction->items as $item) {
                $journalItems[] = [
                    'account_id' => $item->account_id,
                    'credit' => $item->amount,
                ];
            }

            $this->journalService->post(
                date: null,
                referenceType: CashTransaction::class,
                referenceID: $transaction->id,
                description: 'Terima Dana #' . $transaction->number,
                items: $journalItems
            );
        } else if ($transaction->type === CashTransactionTypeEnum::SEND->value) {
            $transaction->load('items', 'costs');
            $creditAccountID = $transaction->from_account_id;
            $creditAmount = $transaction->subtotal + $transaction->tax_amount + $transaction->costs->sum('amount');

            $taxAccountID = AccountSetting::where('company_id', $transaction->company_id)
                ->where('setting_key', AccountSettingEnum::INPUT_TAX->value)
                ->value('account_id');
            $taxAmount = $transaction->tax_amount;

            foreach ($transaction->items as $item) {
                $journalItems[] = [
                    'account_id' => $item->account_id,
                    'debit' => $item->amount,
                ];
            }

            foreach ($transaction->costs as $cost) {
                $journalItems[] = [
                    'account_id' => $cost->account_id,
                    'debit' => $cost->amount,
                ];
            }

            $journalItems[] = [
                'account_id' => $taxAccountID,
                'debit' => $taxAmount,
            ];

            $journalItems[] = [
                'account_id' => $creditAccountID,
                'credit' => $creditAmount,
            ];

            $this->journalService->post(
                date: null,
                referenceType: CashTransaction::class,
                referenceID: $transaction->id,
                description: 'Kirim Dana #' . $transaction->number,
                items: $journalItems
            );
        }
    }

    private function reverseCashJournal(CashTransaction $transaction)
    {
        $journalEntries = JournalEntry::where('reference_type', CashTransaction::class)
            ->where('reference_id', $transaction->id)
            ->get();

        foreach ($journalEntries as $entry) {
            $this->journalService->reverse(
                journalEntryID: $entry->id,
                date: null,
                description: 'Pembalikan Jurnal Untuk Transaksi Kas #' . $transaction->number
            );
        }
    }
}
