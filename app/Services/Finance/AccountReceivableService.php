<?php

namespace App\Services\Finance;

use App\Models\Company;

use App\Enums\SalesInvoiceStatus;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountReceivableService
{
    public function generateARNumber() {
        $prefix = 'SP';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = SalesPayment::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }
    
    public function fetchARTableData(Request $request)
    {
        $query = SalesInvoice::with([
            'customer:id,name,code',
        ])
            ->select(
                'id',
                'number',
                'invoice_date',
                'due_date',
                'customer_id',
                'total_amount',
                'remaining_amount',
                'status'
            );

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $query = $query->orderBy('invoice_date', 'desc')->paginate($request->input('per_page', 10));

        return $query;
    }

    public function fetchPaymentTableData(Request $request, int $id)
    {
        $query = SalesPayment::with([
            'account:id,name,code',
            'creator:id,name',
        ])
            ->where('sales_invoice_id', $id)
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
        $invoice = SalesInvoice::findOrFail($id);

        if ($invoice->remaining_amount <= 0 || $invoice->status === SalesInvoiceStatus::PAID->value) {
            throw ValidationException::withMessages(['error' => 'Invoice ini sudah lunas. Tidak dapat menambahkan pembayaran lagi.']);
        }

        if ($request->status === SalesInvoiceStatus::CANCELLED->value) {
            throw ValidationException::withMessages(['error' => 'Invoice ini dibatalkan. Tidak dapat menambahkan pembayaran.']);
        }

        DB::transaction(function () use ($request, $invoice) {
            SalesPayment::create([
                'company_id' => $invoice->company_id,
                'sales_invoice_id' => $invoice->id,
                'account_id' => $request->account_id,
                'number' => $this->generateARNumber(),
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'amount' => $request->amount,
                'note' => $request->note,
                'created_by' => auth()->id(),
            ]);

            $invoice->remaining_amount -= $request->amount;
            if ($invoice->remaining_amount <= 0) {
                $invoice->status = SalesInvoiceStatus::PAID->value;
            } elseif ($invoice->remaining_amount < $invoice->total_amount) {
                $invoice->status = SalesInvoiceStatus::PARTIAL->value;
            }
            $invoice->save();

            // TODO: Insert Journal Entry for the payment

        });
    }
}
