<?php

namespace App\Services\Finance;

use App\Enums\AccountSettingEnum;
use App\Services\JournalService;
use App\Enums\ExpenseStatus;
use App\Enums\PurchaseInvoiceStatus;
use App\Enums\PayablePaymentReferenceTypeEnum;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\Expense;
use App\Models\PayablePayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountPayableService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }
    public function generateAPNumber()
    {
        $prefix = 'PP';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = PayablePayment::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchInvoiceByID(int $id, string $type)
    {
        if ($type === 'purchase_invoice') {
            return PurchaseInvoice::with(['supplier:id,name,code'])
                ->select(
                    'id',
                    'number',
                    'invoice_date',
                    'due_date',
                    'supplier_id',
                    'total_amount',
                    'remaining_amount',
                    'status'
                )
                ->findOrFail($id);
        } else if ($type === 'expense') {
            $expense = Expense::with(['contact:id,name,code'])
                ->select(
                    'id',
                    'number',
                    'expense_date',
                    'due_date',
                    'contact_id',
                    'total_amount',
                    'remaining_amount',
                    'status'
                )
                ->findOrFail($id);

            $expense->invoice_date = $expense->expense_date; // Map expense_date to invoice_date for consistency
            $expense->supplier_id = $expense->contact_id; // Map contact_id to supplier
            $expense->supplier = $expense->contact; // Map contact to supplier relationship
            return $expense;
        } else {
            throw ValidationException::withMessages(['error' => 'Tipe referensi tidak valid.']);
        }
    }

    public function fetchAPTableData(Request $request)
    {
        $query = PurchaseInvoice::query()
            ->join('contacts', 'contacts.id', '=', 'purchase_invoices.supplier_id')
            ->selectRaw("
                purchase_invoices.id,
                purchase_invoices.number,
                purchase_invoices.invoice_date,
                purchase_invoices.due_date,
                contacts.name as contact_name,
                contacts.code as contact_code,
                purchase_invoices.total_amount,
                purchase_invoices.remaining_amount,
                purchase_invoices.status,
                'purchase_invoice' as type
            ");

        $query2 = Expense::query()
            ->join('contacts', 'contacts.id', '=', 'expenses.contact_id')
            ->selectRaw("
                expenses.id,
                expenses.number,
                expenses.expense_date as invoice_date,
                expenses.due_date,
                contacts.name as contact_name,
                contacts.code as contact_code,
                expenses.total_amount,
                expenses.remaining_amount,
                expenses.status,
                'expense' as type
            ");

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('purchase_invoices.number', 'like', "%{$search}%")
                    ->orWhere('contacts.name', 'like', "%{$search}%")
                    ->orWhere('contacts.code', 'like', "%{$search}%");
            });

            $query2->where(function ($q) use ($search) {
                $q->where('expenses.number', 'like', "%{$search}%")
                    ->orWhere('contacts.name', 'like', "%{$search}%")
                    ->orWhere('contacts.code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_invoices.invoice_date', '>=', $request->input('date_from'));
            $query2->whereDate('expenses.expense_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_invoices.invoice_date', '<=', $request->input('date_to'));
            $query2->whereDate('expenses.expense_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('purchase_invoices.status', $request->input('status'));
            $query2->where('expenses.status', $request->input('status'));
        }

        $query = $query->unionAll($query2);

        $query = $query->orderBy('invoice_date', 'desc')->paginate($request->input('per_page', 10));

        return $query;
    }

    public function fetchPaymentTableData(Request $request)
    {
        $query = PayablePayment::with([
            'account:id,name,code',
            'creator:id,username',
        ])
            ->where('reference_type', PayablePaymentReferenceTypeEnum::from($request->reference_type)->value)
            ->where('reference_id', $request->reference_id)
            ->select(
                'id',
                'number',
                'payment_date',
                'payment_method',
                'reference_number',
                'amount',
                'note',
                'created_by'
            );

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $query = $query->orderBy('payment_date', 'desc')->paginate($request->input('per_page', 10));

        return $query;
    }

    public function storePayment(Request $request, int $id)
    {
        if ($request->reference_type === PayablePaymentReferenceTypeEnum::PURCHASE_INVOICE->value) {
            $invoice = PurchaseInvoice::findOrFail($id);
        } elseif ($request->reference_type === PayablePaymentReferenceTypeEnum::EXPENSE->value) {
            $invoice = Expense::findOrFail($id);
        } else {
            throw ValidationException::withMessages(['error' => 'Tipe referensi tidak valid.']);
        }

            DB::transaction(function () use ($request, $invoice) {
                $payment = PayablePayment::create([
                    'company_id' => $invoice->company_id,
                    'reference_id' => $invoice->id,
                    'reference_type' => PayablePaymentReferenceTypeEnum::from($request->reference_type)->value,
                    'account_id' => $request->account_id,
                    'number' => $this->generateAPNumber(),
                    'payment_date' => $request->payment_date,
                    'payment_method' => $request->payment_method,
                    'reference_number' => $request->reference_number,
                    'amount' => $request->amount,
                    'note' => $request->note,
                    'created_by' => auth()->id(),
                ]);

                $invoice->remaining_amount -= $request->amount;
                if ($invoice->remaining_amount <= 0) {
                    $invoice->status = $request->reference_type === PayablePaymentReferenceTypeEnum::PURCHASE_INVOICE->value
                        ? PurchaseInvoiceStatus::PAID->value
                        : ExpenseStatus::PAID->value;
                } elseif ($invoice->remaining_amount < $invoice->total_amount) {
                    $invoice->status = $request->reference_type === PayablePaymentReferenceTypeEnum::PURCHASE_INVOICE->value
                        ? PurchaseInvoiceStatus::PARTIAL->value
                        : ExpenseStatus::PARTIAL->value;
                }
                $invoice->save();

                $this->postPaymentJournal($payment);
            });
    }

    private function postPaymentJournal(PayablePayment $payment) {
        $payableID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::ACCOUNT_PAYABLE->value)
            ->value('account_id');

        $cashBankID = $payment->account_id;
        $amount = $payment->amount;

        $journalItems = [
            [
                'account_id' => $payableID,
                'debit' => $amount,
            ],
            [
                'account_id' => $cashBankID,
                'credit' => $amount,
            ],
        ];

        $this->journalService->post(
            date: null,
            referenceType: PayablePayment::class,
            referenceID: $payment->id,
            description: 'Pembayaran Hutang #' . $payment->number,
            items: $journalItems
        );
    }
}
