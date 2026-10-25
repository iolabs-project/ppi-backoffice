<?php

namespace App\Services\Master;

use App\Models\Contact;
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactService
{
    public function fetchOptionData(Request $request)
    {
        $data = Contact::select(
            'id',
            'code',
            'name',
        )
            ->where('company_id', config('context.selected_company_id'))
            ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            switch ($request->type) {
                case 'customer':
                    $data->where('is_customer', true);
                    break;
                case 'supplier':
                    $data->where('is_supplier', true);
                    break;
                case 'employee':
                    $data->where('is_employee', true);
                    break;
                default:
                    break;
            }
        }



        return $data->get();
    }

    public function fetchContactData(string|null $type = null)
    {
        $data = Contact::select(
            'id',
            'code',
            'name',
        )
            ->where('company_id', config('context.selected_company_id'))
            ->whereNull('deleted_at');

        if ($type) {
            switch ($type) {
                case 'customer':
                    $data->addSelect('transportation_cost')->where('is_customer', true);
                    break;
                case 'supplier':
                    $data->where('is_supplier', true);
                    break;
                case 'employee':
                    $data->where('is_employee', true);
                    break;
                default:
                    break;
            }
        }

        return $data->get();
    }

    public function fetchContactTableData(Request $request)
    {
        $data = Contact::select(
            'id',
            'code',
            'name',
            'address',
            'phone',
            'email',
            'city',
            'is_customer',
            'is_supplier',
            'is_employee',
            'deleted_at',
            'transportation_cost'
        )
            ->where('company_id', config('context.selected_company_id'));

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            switch ($request->type) {
                case 'customer':
                    $data->where('is_customer', true);
                    break;
                case 'supplier':
                    $data->where('is_supplier', true);
                    break;
                case 'employee':
                    $data->where('is_employee', true);
                    break;
                default:
                    break;
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $data->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $data->whereNotNull('deleted_at');
            }
        }

        $data = $data->orderBy('name', 'asc')->paginate($request->input('per_page', 10));
        return $data;
    }

    public function fetchContactById(int $id)
    {
        return Contact::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();
    }

    /**
     * Documents this contact appears on (newest first) and what is still owed either way.
     */
    public function fetchContactActivity(int $id): array
    {
        $companyID = config('context.selected_company_id');
        $documents = [
            // table, contact column, date column, label, detail route (a goods receipt has no amount of its own)
            ['purchase_orders', 'supplier_id', 'order_date', 'Purchase Order', 'purchasings.purchase_orders.show'],
            ['goods_receipts', 'supplier_id', 'receipt_date', 'Penerimaan Barang', 'purchasings.goods_receipts.show'],
            ['purchase_invoices', 'supplier_id', 'invoice_date', 'Tagihan Pembelian', 'purchasings.purchase_invoices.show'],
            ['sales_orders', 'customer_id', 'order_date', 'Sales Order', 'sales.sales_orders.show'],
            ['delivery_orders', 'customer_id', 'delivery_date', 'Pengiriman', 'sales.delivery_orders.show'],
            ['sales_invoices', 'customer_id', 'invoice_date', 'Tagihan Penjualan', 'sales.sales_invoices.show'],
            ['expenses', 'contact_id', 'expense_date', 'Biaya', 'expenses.show'],
        ];

        $union = null;
        foreach ($documents as [$table, $contactColumn, $dateColumn, $label, $route]) {
            $query = DB::table($table)
                ->where('company_id', $companyID)
                ->where($contactColumn, $id)
                ->selectRaw("id, number, status, " . ($table === 'goods_receipts' ? 'NULL' : 'total_amount') . " as total_amount, {$dateColumn} as document_date, ? as type, ? as route", [$label, $route]);
            $union = $union ? $union->unionAll($query) : $query;
        }
        $all = DB::query()->fromSub($union, 'documents');

        $outstanding = fn (string $table, string $contactColumn) => (float) DB::table($table)
            ->where('company_id', $companyID)
            ->where($contactColumn, $id)
            ->whereIn('status', ['open', 'partial'])
            ->sum('remaining_amount');

        return [
            'history' => (clone $all)->orderByDesc('document_date')->orderByDesc('id')->limit(20)->get(),
            'transactionsThisMonth' => (clone $all)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('document_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            // What the company still owes this contact, and what the contact still owes the company
            'payable' => $outstanding('purchase_invoices', 'supplier_id') + $outstanding('expenses', 'contact_id'),
            'receivable' => $outstanding('sales_invoices', 'customer_id'),
        ];
    }

    public function storeContact(Request $request)
    {
        $checkExistingCode = Contact::where('company_id', config('context.selected_company_id'))
            ->where('code', $request->code)
            ->exists();

        if ($checkExistingCode) {
            throw ValidationException::withMessages([
                'code' => 'Kode kontak sudah digunakan. Silakan gunakan kode lain.',
            ]);
        }

        return Contact::create([
            'company_id' => config('context.selected_company_id'),
            'code' => $request->filled('code') ? $request->code : $this->generateContactCode(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'note' => $request->note,
            'is_customer' => $request->is_customer ?? false,
            'is_supplier' => $request->is_supplier ?? false,
            'is_employee' => $request->is_employee ?? false,
            'transportation_cost' => $request->input('transportation_cost', 0),
        ]);
    }

    public function updateContact(Request $request, int $id)
    {
        $contact = Contact::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        if (!$contact) {
            throw ValidationException::withMessages([
                'id' => 'Kontak tidak ditemukan.',
            ]);
        }

        if ($request->filled('code') && $contact->code !== $request->code) {
            $checkExistingCode = Contact::where('company_id', config('context.selected_company_id'))
                ->where('code', $request->code)
                ->exists();

            if ($checkExistingCode) {
                throw ValidationException::withMessages([
                    'code' => 'Kode kontak sudah digunakan. Silakan gunakan kode lain.',
                ]);
            }
        }

        $contact->update([
            'code' => $request->filled('code') ? $request->code : $contact->code,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'note' => $request->note,
            'is_customer' => $request->is_customer ?? false,
            'is_supplier' => $request->is_supplier ?? false,
            'is_employee' => $request->is_employee ?? false,
            'transportation_cost' => $request->input('transportation_cost', 0),
        ]);
    }

    // Next "C-001" style code for the selected company
    public function generateContactCode(): string
    {
        return app(NumberingService::class)->masterCode('contact', Contact::class);
    }

    public function toggleContactStatus(int $id)
    {
        $contact = Contact::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        if (!$contact) {
            throw ValidationException::withMessages([
                'id' => 'Kontak tidak ditemukan.',
            ]);
        }

        if ($contact->deleted_at) {
            $contact->deleted_at = null;
            $contact->save();
        } else {
            $contact->deleted_at = now();
            $contact->save();
        }
    }

    
}
