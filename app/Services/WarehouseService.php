<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseService
{
    public function fetchOptionData(Request $request)
    {
        $data = Warehouse::select(
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

        return $data->get();
    }

    public function fetchWarehouseData()
    {
        $data = Warehouse::select(
            'id',
            'code',
            'name',
        )
        ->where('company_id', config('context.selected_company_id'))
        ->whereNull('deleted_at')
        ->get();

        return $data;
    }
}