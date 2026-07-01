<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Http\Request;

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

    public function fetchContactData(string|null $type)
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
}