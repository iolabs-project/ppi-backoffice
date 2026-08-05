<?php

namespace App\Services;

use App\Enums\AccountSettingEnum;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentTerm;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\DeliveryOrder;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\ExpenseCost;
use App\Models\GoodsReceipt;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }
    public function generateNumber(): string
    {
        $prefix = 'EXP';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = Expense::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchExpenseByID(int $id): ?Expense
    {
        return Expense::with([
            'company',
            'contact',
            'items.account',
            'costs.account',
            'creator'
        ])
            ->select(
                'company_id',
                'contact_id',
                'number',
                'reference_number',
                'expense_date',
                'due_date',
                'payment_terms',
                'status',
                'subtotal',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'total_amount',
                'remaining_amount',
                'note',
                'created_by'
            )
            ->find($id);
    }

    public function fetchTableData(Request $request)
    {
        $query = Expense::with([
            'contact:id,name',
        ])
            ->select(
                'id',
                'number',
                'reference_number',
                'expense_date',
                'contact_id',
                'status',
                'total_amount',
                'remaining_amount'
            );

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }


        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $query = $query->orderBy('expense_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function storeExpense(Request $request)
    {
        DB::transaction(function () use ($request) {
            $expense = Expense::create([
                'company_id' => config('context.selected_company_id'),
                'contact_id' => $request->input('contact_id'),
                'number' => $this->generateNumber(),
                'reference_number' => $request->input('reference_number'),
                'expense_date' => $request->input('expense_date'),
                'due_date' => $request->filled('payment_terms') ? now()->addDays(PaymentTerm::day($request->input('payment_terms'))) : null,
                'payment_terms' => $request->input('payment_terms'),
                'status' => ExpenseStatus::DRAFT->value,
                'subtotal' => 0,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'remaining_amount' => 0,
                'note' => $request->input('note'),
                'created_by' => Auth::id(),
            ]);
            $subtotal = 0;
            foreach ($request->input('items', []) as $itemData) {
                ExpenseItem::create([
                    'expense_id' => $expense->id,
                    'account_id' => $itemData['account_id'],
                    'description' => $itemData['description'] ?? null,
                    'amount' => $itemData['amount'] ?? 0,
                ]);

                $subtotal += $itemData['amount'] ?? 0;
            }
            $discountPercentage = $request->input('discount_percentage', 0);
            $discountAmount = ($discountPercentage / 100) * $subtotal;
            $taxPercentage = $request->input('tax_percentage', 0);
            $taxAmount = ($taxPercentage / 100) * ($subtotal - $discountAmount);

            $chargeAmount = 0;
            foreach ($request->input('costs', []) as $chargeData) {
                ExpenseCost::create([
                    'expense_id' => $expense->id,
                    'account_id' => $chargeData['account_id'],
                    'description' => $chargeData['description'] ?? null,
                    'amount' => $chargeData['amount'] ?? 0,
                    'is_taxable' => $chargeData['is_taxable'] ?? false,
                ]);

                $chargeAmount += $chargeData['amount'] ?? 0;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount - $chargeAmount;
            $expense->update([
                'subtotal' => $subtotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount,
            ]);
        });
    }

    public function storeExpenseFromGoodsReceipt(int $id)
    {
        // $costs = GoodsReceiptCost::where('goods_receipt_id', $id)->get();
        $goodsReceipt = GoodsReceipt::with([
            'costs' => function ($query) {
                $query->where('billed_by', 'third_party');
            }
        ])->findOrFail($id);
        $now = now();

        DB::transaction(function () use ($goodsReceipt, $now) {
            foreach ($goodsReceipt->costs as $cost) {
                $expense = Expense::create([
                    'company_id' => config('context.selected_company_id'),
                    'number' => $this->generateNumber(),
                    'reference_number' => $goodsReceipt->number,
                    'expense_date' => $now,
                    'status' => ExpenseStatus::DRAFT->value,
                    'subtotal' => $cost->amount,
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $cost->amount,
                    'remaining_amount' => $cost->amount,
                    'created_by' => Auth::id(),,
                ]);

                ExpenseItem::create([
                    'expense_id' => $expense->id,
                    'account_id' => $cost->account_id,
                    'description' => $cost->description,
                    'amount' => $cost->amount,
                ]);
            }
        });
    }

    public function storeExpenseFromDeliveryOrder(int $id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'costs' => function ($query) {
                $query->where('billed_by', 'third_party');
            }
        ])->findOrFail($id);

        DB::transaction(function () use ($deliveryOrder) {
            foreach ($deliveryOrder->costs as $cost) {
                $expense = Expense::create([
                    'company_id' => config('context.selected_company_id'),
                    'number' => $this->generateNumber(),
                    'reference_number' => $deliveryOrder->number,
                    'expense_date' => now(),
                    'status' => ExpenseStatus::DRAFT->value,
                    'subtotal' => $cost->amount,
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $cost->amount,
                    'remaining_amount' => $cost->amount,
                    'created_by' => Auth::id(),
                ]);

                ExpenseItem::create([
                    'expense_id' => $expense->id,
                    'account_id' => $cost->account_id,
                    'description' => $cost->description,
                    'amount' => $cost->amount,
                ]);
            }
        });
    }

    public function updateExpense(Request $request, int $id)
    {

        DB::transaction(function () use ($request, $id) {
            $expense = Expense::findOrFail($id);
            $expense->update([
                'contact_id' => $request->input('contact_id'),
                'reference_number' => $request->input('reference_number'),
                'expense_date' => $request->input('expense_date'),
                'due_date' => $request->filled('payment_terms') ? now()->addDays(PaymentTerm::day($request->input('payment_terms'))) : null,
                'payment_terms' => $request->input('payment_terms'),
                'status' => $request->input('status', 'draft'),
                'note' => $request->input('note'),
            ]);

            // Delete existing items and costs
            $expense->items()->delete();
            $expense->costs()->delete();

            // Recalculate subtotal, discount, tax, and total
            $subtotal = 0;
            foreach ($request->input('items', []) as $itemData) {
                ExpenseItem::create([
                    'expense_id' => $expense->id,
                    'account_id' => $itemData['account_id'],
                    'description' => $itemData['description'] ?? null,
                    'amount' => $itemData['amount'] ?? 0,
                ]);

                $subtotal += $itemData['amount'] ?? 0;
            }
            $discountPercentage = $request->input('discount_percentage', 0);
            $discountAmount = ($discountPercentage / 100) * $subtotal;
            $taxPercentage = $request->input('tax_percentage', 0);
            $taxAmount = ($taxPercentage / 100) * ($subtotal - $discountAmount);

            $chargeAmount = 0;
            foreach ($request->input('costs', []) as $chargeData) {
                ExpenseCost::create([
                    'expense_id' => $expense->id,
                    'account_id' => $chargeData['account_id'],
                    'description' => $chargeData['description'] ?? null,
                    'amount' => $chargeData['amount'] ?? 0,
                    'is_taxable' => $chargeData['is_taxable'] ?? false,
                ]);

                $chargeAmount += $chargeData['amount'] ?? 0;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount - $chargeAmount;
            $expense->update([
                'subtotal' => $subtotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount, // Assuming no payments have been made yet
            ]);

            if ($request->input('status') === ExpenseStatus::OPEN->value) {
                $this->postExpenseJournal($expense);
            }
        });
    }

    public function openExpense(int $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->update(['status' => ExpenseStatus::OPEN->value]);

        // TODO: Create journal entry for the expense
    }

    public function cancelExpense(int $id)
    {
        $expense = Expense::findOrFail($id);
        if ($expense->status === ExpenseStatus::PARTIAL->value || $expense->status === ExpenseStatus::PAID->value) {
            throw ValidationException::withMessages(['status' => 'Biaya ini tidak dapat dibatalkan.']);
        }

        if ($expense->status === ExpenseStatus::CANCELLED->value) {
            throw ValidationException::withMessages(['status' => 'Biaya ini sudah dibatalkan.']);
        }

        DB::transaction(function () use ($expense) {
            if ($expense->status === ExpenseStatus::OPEN->value) {
                $this->reverseExpenseJournal($expense);
            }

            // Update the expense status to cancelled
            $expense->update(['status' => ExpenseStatus::CANCELLED->value]);
        });
    }

    private function postExpenseJournal(Expense $expense)
    {
        $payableAmount = 0;
        $payableID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::ACCOUNT_PAYABLE->value)
            ->value('account_id');

        $taxID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::INPUT_TAX->value)
            ->value('account_id');
        $taxAmount = $expense->tax_amount;

        $journalItems = [];


        foreach ($expense->items as $item) {
            $journalItems[] = [
                'account_id' => $item->account_id,
                'debit' => $item->amount,
                'description' => $item->description ?? null,
            ];
            $payableAmount += $item->amount;
        }

        if ($taxAmount > 0) {
            $journalItems[] = [
                'account_id' => $taxID,
                'debit' => $taxAmount,
                'description' => 'Pajak atas Biaya #' . $expense->number,
            ];
            $payableAmount += $taxAmount;
        }

        foreach ($expense->costs as $cost) {
            $journalItems[] = [
                'account_id' => $cost->account_id,
                'credit' => $cost->amount,
                'description' => $cost->description,
            ];

            $payableAmount -= $cost->amount;
        }

        $journalItems[] = [
            'account_id' => $payableID,
            'credit' => $payableAmount,
            'description' => 'Hutang Biaya #' . $expense->number,
        ];

        $this->journalService->post(
            date: null,
            referenceType: Expense::class,
            referenceID: $expense->id,
            description: 'Biaya #' . $expense->number,
            items: $journalItems
        );
    }

    private function reverseExpenseJournal(Expense $expense)
    {
        $journalEntryID = JournalEntry::where('reference_type', Expense::class)
            ->where('reference_id', $expense->id)
            ->value('id');
        $this->journalService->reverse(
            journalEntryID: $journalEntryID,
            date: null,
            description: 'Pembalikan Biaya #' . $expense->number
        );
    }
}
