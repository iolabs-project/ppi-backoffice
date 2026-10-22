<?php

namespace App\Services\Master;

use App\Models\Contact;
use Illuminate\Http\Request;
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
            'code' => $request->filled('code') ? $request->code : $this->generateContactCode($request),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'note' => $request->note,
            'receivable_account_id' => $request->receivable_account_id,
            'payable_account_id' => $request->payable_account_id,
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
            'receivable_account_id' => $request->receivable_account_id,
            'payable_account_id' => $request->payable_account_id,
            'is_customer' => $request->is_customer ?? false,
            'is_supplier' => $request->is_supplier ?? false,
            'is_employee' => $request->is_employee ?? false,
            'transportation_cost' => $request->input('transportation_cost', 0),
        ]);
    }

    // Next "CUST-001" / "SUP-001" / "EMP-001" style code, prefixed by the contact's first type
    public function generateContactCode(Request $request): string
    {
        $prefix = match (true) {
            (bool) $request->is_customer => 'CUST',
            (bool) $request->is_supplier => 'SUP',
            (bool) $request->is_employee => 'EMP',
            default => 'CON',
        };

        $last = Contact::where('company_id', config('context.selected_company_id'))
            ->where('code', 'like', $prefix . '-%')
            ->pluck('code')
            ->map(fn($code) => (int) substr($code, strlen($prefix) + 1))
            ->max() ?? 0;

        return $prefix . '-' . str_pad($last + 1, 3, '0', STR_PAD_LEFT);
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
