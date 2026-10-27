<?php

namespace App\Services\Finance;

use App\Enums\AccountSettingEnum;
use App\Enums\CashTransactionStatusEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Enums\ReceivablePaymentReferenceTypeEnum;
use App\Services\NumberingService;
use App\Services\JournalService;
use App\Services\Finance\CashService;
use App\Enums\SalesInvoiceStatus;
use App\Models\AccountSetting;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\ReceivablePayment;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AccountReceivableService
{
    private JournalService $journalService;
    private CashService $cashService;
    public function __construct(JournalService $journalService, CashService $cashService)
    {
        $this->journalService = $journalService;
        $this->cashService = $cashService;
    }
    public function generateARNumber()
    {
        return app(NumberingService::class)->document('account_receivable', ReceivablePayment::class);
    }

    public function fetchInvoiceByID(int $id, ?string $type)
    {
        if ($type === 'sales_invoice') {
            return SalesInvoice::with(['customer:id,name,code'])
                ->select(
                    'id',
                    'number',
                    'invoice_date',
                    'due_date',
                    'customer_id',
                    'total_amount',
                    'remaining_amount',
                    'status'
                )
                ->findOrFail($id);
        } else {
            // Only reached from a page URL, so an unknown type is a page that doesn't exist
            abort(404, 'Tipe referensi tidak valid.');
        }
    }

    public function fetchARTableData(Request $request)
    {
        $query = SalesInvoice::query()
            ->join('contacts', 'contacts.id', '=', 'sales_invoices.customer_id')
            ->selectRaw("
                sales_invoices.id,
                sales_invoices.number,
                sales_invoices.invoice_date,
                sales_invoices.due_date,
                contacts.name as contact_name,
                contacts.code as contact_code,
                sales_invoices.total_amount,
                sales_invoices.remaining_amount,
                sales_invoices.status,
                'sales_invoice' as type
            ");

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('sales_invoices.number', 'like', "%{$search}%")
                    ->orWhere('contacts.name', 'like', "%{$search}%")
                    ->orWhere('contacts.code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('sales_invoices.invoice_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('sales_invoices.invoice_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('sales_invoices.status', $request->input('status'));
        }


        $query = $query->orderBy('sales_invoices.invoice_date', 'desc')->orderBy('sales_invoices.id', 'desc')->paginate($request->input('per_page', 10));

        return $query;
    }

    public function fetchPaymentTableData(Request $request)
    {
        $query = ReceivablePayment::with([
            'account:id,name,code',
            'creator:id,username',
        ])
            ->where('reference_type', ReceivablePaymentReferenceTypeEnum::from($request->reference_type)->value)
            ->where('reference_id', $request->reference_id)
            ->select(
                'id',
                'number',
                'payment_date',
                'payment_method',
                'reference_number',
                'amount',
                'note',
                'voided_at',
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

        $query = $query->orderBy('number', 'desc')->paginate($request->input('per_page', 10));

        return $query;
    }

    public function storePayment(Request $request, int $id)
    {
        if ($request->reference_type !== ReceivablePaymentReferenceTypeEnum::SALES_INVOICE->value) {
            throw ValidationException::withMessages(['error' => 'Tipe referensi tidak valid.']);
        }

        // The token is issued when the payment form opens, so a double-click or retry is recorded once
        run_once('payment:' . $request->input('idempotency_key'), fn () => DB::transaction(function () use ($request, $id) {
            // Lock the invoice so two submits can't both pass the remaining-amount check
            $invoice = SalesInvoice::lockForUpdate()->findOrFail($id);

            if (!in_array($invoice->status, [SalesInvoiceStatus::OPEN->value, SalesInvoiceStatus::PARTIAL->value], true)) {
                throw ValidationException::withMessages(['amount' => 'Pembayaran hanya dapat dicatat untuk tagihan yang berstatus Open atau Partial.']);
            }

            if ($request->amount > $invoice->remaining_amount) {
                throw ValidationException::withMessages(['amount' => 'Jumlah pembayaran melebihi sisa piutang.']);
            }

            $payment = ReceivablePayment::create([
                'company_id' => $invoice->company_id,
                'reference_id' => $invoice->id,
                'reference_type' => ReceivablePaymentReferenceTypeEnum::from($request->reference_type)->value,
                'account_id' => $request->account_id,
                'number' => $this->generateARNumber(),
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'note' => $request->note,
                'created_by' => Auth::id(),
            ]);

            $invoice->remaining_amount -= $request->amount;
            if ($invoice->remaining_amount <= 0) {
                $invoice->status = $request->reference_type === ReceivablePaymentReferenceTypeEnum::SALES_INVOICE->value
                    ? SalesInvoiceStatus::PAID->value
                    : SalesInvoiceStatus::PAID->value;
            } elseif ($invoice->remaining_amount < $invoice->total_amount) {
                $invoice->status = $request->reference_type === ReceivablePaymentReferenceTypeEnum::SALES_INVOICE->value
                    ? SalesInvoiceStatus::PARTIAL->value
                    : SalesInvoiceStatus::PARTIAL->value;
            }
            $invoice->save();

            $this->postCashTransaction($payment);
            $this->postPaymentJournal($payment);
        }), 'Pembayaran ini sudah tercatat.');
    }

    public function voidPayment(int $paymentID)
    {
        DB::transaction(function () use ($paymentID) {
            $payment = ReceivablePayment::lockForUpdate()->findOrFail($paymentID);
            if ($payment->voided_at) {
                throw ValidationException::withMessages(['error' => 'Pembayaran ini sudah di-void.']);
            }

            $invoice = SalesInvoice::lockForUpdate()->findOrFail($payment->reference_id);
            if (!in_array($invoice->status, [SalesInvoiceStatus::PARTIAL->value, SalesInvoiceStatus::PAID->value], true)) {
                throw ValidationException::withMessages(['error' => 'Pembayaran hanya dapat di-void untuk tagihan yang berstatus Partial atau Paid.']);
            }

            // Voiding a receipt takes the money back out, so the account must still hold it
            if ($this->cashService->fetchAccountBalance($payment->account_id) < $payment->amount) {
                throw ValidationException::withMessages(['error' => 'Saldo akun kas tidak mencukupi untuk void pembayaran ini.']);
            }

            $invoice->remaining_amount = min($invoice->total_amount, round($invoice->remaining_amount + $payment->amount, 4));
            $invoice->status = $invoice->remaining_amount < $invoice->total_amount ? SalesInvoiceStatus::PARTIAL->value : SalesInvoiceStatus::OPEN->value;
            $invoice->save();

            $journalEntry = JournalEntry::where('reference_type', ReceivablePayment::class)->where('reference_id', $payment->id)->first();
            if ($journalEntry) {
                $this->journalService->reverse(
                    journalEntryID: $journalEntry->id,
                    date: null,
                    description: 'Void Pembayaran Piutang #' . $payment->number
                );
            }

            // The cash transaction points at the invoice, so its description is what ties it to this payment
            CashTransaction::where('reference_type', SalesInvoice::class)
                ->where('reference_id', $payment->reference_id)
                ->where('description', 'Pembayaran Piutang #' . $payment->number)
                ->first()
                ?->update(['status' => CashTransactionStatusEnum::CANCELLED->value]);

            $payment->update(['voided_at' => now()]);
        });
    }

    private function postPaymentJournal(ReceivablePayment $payment)
    {
        $receivableID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::ACCOUNT_RECEIVABLE->value)
            ->value('account_id');

        $cashBankID = $payment->account_id;
        $amount = $payment->amount;

        $journalItems = [
            [
                'account_id' => $cashBankID,
                'debit' => $amount,
            ],
            [
                'account_id' => $receivableID,
                'credit' => $amount,
            ],
        ];

        $this->journalService->post(
            date: null,
            referenceType: ReceivablePayment::class,
            referenceID: $payment->id,
            description: 'Pembayaran Piutang #' . $payment->number,
            items: $journalItems
        );
    }

    private function postCashTransaction(ReceivablePayment $payment)
    {
        $request = new Request([
            'to_account_id' => $payment->account_id,
            'contact_id' => SalesInvoice::find($payment->reference_id)->customer_id,
            'transaction_date' => $payment->payment_date,
            'description' => 'Pembayaran Piutang #' . $payment->number,
            'type' => CashTransactionTypeEnum::RECEIVE->value,
            'status' => CashTransactionStatusEnum::POSTED->value,
            'subtotal' => $payment->amount,
            'reference_type' => $payment->reference_type === ReceivablePaymentReferenceTypeEnum::SALES_INVOICE->value
                ? SalesInvoice::class
                : null,
            'reference_id' => $payment->reference_id,
        ]);

        $this->cashService->storeTransaction($request, $payment->company_id);
    }
}
