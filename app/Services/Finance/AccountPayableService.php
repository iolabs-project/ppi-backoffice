<?php

namespace App\Services\Finance;

use App\Enums\PurchaseInvoiceStatus;
use App\Models\Company;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountPayableService
{
    public function generateAPNumber() {
        $prefix = 'PP';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = PurchasePayment::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }
    
    public function derivePaymentStatus(string $status, $dueDate): string
    {
        return match ($status) {
            PurchaseInvoiceStatus::PAID->value => 'paid',
            PurchaseInvoiceStatus::PARTIAL->value => 'partial',
            PurchaseInvoiceStatus::CANCELLED->value => 'cancelled',
            PurchaseInvoiceStatus::DRAFT->value => 'draft',
            default => ($dueDate && $dueDate->isPast()) ? 'unpaid' : 'not-yet-due',
        };
    }

    public function fetchAPTableData(Request $request)
    {
        $query = PurchaseInvoice::with([
            'supplier:id,name,code',
        ])
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
            ->whereIn('status', [
                PurchaseInvoiceStatus::OPEN->value,
                PurchaseInvoiceStatus::PARTIAL->value,
                PurchaseInvoiceStatus::PAID->value,
            ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $today = now()->toDateString();
            match ($request->input('status')) {
                'paid' => $query->where('status', PurchaseInvoiceStatus::PAID->value),
                'partial' => $query->where('status', PurchaseInvoiceStatus::PARTIAL->value),
                'not-yet-due' => $query->where('status', PurchaseInvoiceStatus::OPEN->value)
                    ->whereDate('due_date', '>=', $today),
                'unpaid' => $query->where('status', PurchaseInvoiceStatus::OPEN->value)
                    ->whereDate('due_date', '<', $today),
                default => null,
            };
        }

        $query = $query->orderBy('invoice_date', 'desc')->paginate($request->input('per_page', 10));

        $query->getCollection()->transform(function ($invoice) {
            $invoice->display_status = $this->derivePaymentStatus($invoice->status, $invoice->due_date);
            return $invoice;
        });

        return $query;
    }

    public function fetchPaymentTableData(Request $request, int $id)
    {
        $query = PurchasePayment::with([
            'account:id,name,code',
            'creator:id,name',
        ])
            ->where('purchase_invoice_id', $id)
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
        $invoice = PurchaseInvoice::findOrFail($id);

        if ($invoice->remaining_amount <= 0 || $invoice->status === PurchaseInvoiceStatus::PAID->value) {
            throw ValidationException::withMessages(['error' => 'Invoice ini sudah lunas. Tidak dapat menambahkan pembayaran lagi.']);
        }

        if ($invoice->status === PurchaseInvoiceStatus::CANCELLED->value) {
            throw ValidationException::withMessages(['error' => 'Invoice ini dibatalkan. Tidak dapat menambahkan pembayaran.']);
        }

        if ($request->amount > $invoice->remaining_amount) {
            throw ValidationException::withMessages(['amount' => 'Jumlah pembayaran tidak boleh melebihi sisa outstanding (' . fmt_rp($invoice->remaining_amount) . ').']);
        }

        DB::transaction(function () use ($request, $invoice) {
            PurchasePayment::create([
                'company_id' => $invoice->company_id,
                'purchase_invoice_id' => $invoice->id,
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
                $invoice->status = PurchaseInvoiceStatus::PAID->value;
            } elseif ($invoice->remaining_amount < $invoice->total_amount) {
                $invoice->status = PurchaseInvoiceStatus::PARTIAL->value;
            }
            $invoice->save();

            // TODO: Insert Journal Entry for the payment

        });
    }
}
