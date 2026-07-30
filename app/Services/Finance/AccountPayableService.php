<?php

namespace App\Services\Finance;

use App\Enums\ExpenseStatus;
use App\Enums\PurchaseInvoiceStatus;
use App\Models\Company;
use App\Models\Expense;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountPayableService
{
    public function generateAPNumber()
    {
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
            ")
            ->whereIn('purchase_invoices.status', [
                PurchaseInvoiceStatus::OPEN->value,
                PurchaseInvoiceStatus::PARTIAL->value,
                PurchaseInvoiceStatus::PAID->value,
            ]);

        $query2 = Expense::query()
            ->join('contacts', 'contacts.id', '=', 'expenses.contact_id')
            ->selectRaw("
                expenses.id,
                expenses.number,
                expenses.invoice_date,
                expenses.due_date,
                contacts.name as contact_name,
                contacts.code as contact_code,
                expenses.total_amount,
                expenses.remaining_amount,
                expenses.status,
                'expense' as type
            ")
            ->whereIn('expenses.status', [
                ExpenseStatus::OPEN->value,
                ExpenseStatus::PARTIAL->value,
                ExpenseStatus::PAID->value,
            ]);

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
            $query2->whereDate('expenses.invoice_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_invoices.invoice_date', '<=', $request->input('date_to'));
            $query2->whereDate('expenses.invoice_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $today = now()->toDateString();

            $applyStatusFilter = function ($builder, string $table, string $statusColumn) use ($request, $today) {
                match ($request->input('status')) {
                    'paid' => $builder->where($statusColumn, 'paid'),
                    'partial' => $builder->where($statusColumn, 'partial'),
                    'not-yet-due' => $builder->where($statusColumn, 'open')
                        ->whereDate("$table.due_date", '>=', $today),
                    'unpaid' => $builder->where($statusColumn, 'open')
                        ->whereDate("$table.due_date", '<', $today),
                    default => null,
                };
            };

            $applyStatusFilter($query, 'purchase_invoices', 'purchase_invoices.status');
            $applyStatusFilter($query2, 'expenses', 'expenses.status');
        }

        $query = $query->unionAll($query2);

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
