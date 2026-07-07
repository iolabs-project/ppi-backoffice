<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function fetchWarehouseTableData(Request $request)
    {
        $data = Warehouse::select(
            'id',
            'code',
            'name',
            'address',
            'note',
        )
        ->where('company_id', config('context.selected_company_id'))
        ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%')
                    ->orWhere('note', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $data->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $data->whereNotNull('deleted_at');
            }
        }

        $data = $data->orderBy('code', 'asc')->paginate($request->input('per_page', 10));
        return $data;
    }

    public function fetchWarehouseByID(int $id)
    {
        $data = Warehouse::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        return $data;
    }

    public function storeWarehouse(Request $request)
    {
        $checkExistingCode = Warehouse::where('company_id', config('context.selected_company_id'))
            ->where('code', $request->code)
            ->exists();

        if ($checkExistingCode) {
            throw ValidationException::withMessages([
                'code' => ['Kode gudang sudah digunakan.'],
            ]);
        }

        $checkExistingName = Warehouse::where('company_id', config('context.selected_company_id'))
            ->where('name', $request->name)
            ->exists();

        if ($checkExistingName) {
            throw ValidationException::withMessages([
                'name' => ['Nama gudang sudah digunakan.'],
            ]);
        }

        Warehouse::create([
            'company_id' => config('context.selected_company_id'),
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
            'note' => $request->note,
        ]);
    }

    public function updateWarehouse(Request $request, int $id)
    {
        $warehouse = Warehouse::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        if (!$warehouse) {
            throw ValidationException::withMessages([
                'id' => ['Gudang tidak ditemukan.'],
            ]);
        }

        $checkExistingCode = Warehouse::where('company_id', config('context.selected_company_id'))
            ->where('code', $request->code)
            ->where('id', '!=', $id)
            ->exists();

        if ($checkExistingCode) {
            throw ValidationException::withMessages([
                'code' => ['Kode gudang sudah digunakan.'],
            ]);
        }

        $checkExistingName = Warehouse::where('company_id', config('context.selected_company_id'))
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();

        if ($checkExistingName) {
            throw ValidationException::withMessages([
                'name' => ['Nama gudang sudah digunakan.'],
            ]);
        }

        $warehouse->update([
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
            'note' => $request->note,
        ]);
    }

    public function toggleWarehouseStatus(int $id)
    {
        $warehouse = Warehouse::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        if (!$warehouse) {
            throw ValidationException::withMessages([
                'id' => ['Gudang tidak ditemukan.'],
            ]);
        }

        if ($warehouse->deleted_at) {
            $warehouse->update(['deleted_at' => null]);
        } else {
            $warehouse->update(['deleted_at' => now()]);
        }
    }
}