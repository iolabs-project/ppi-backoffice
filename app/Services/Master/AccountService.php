<?php

namespace App\Services\Master;

use App\Models\AccountCategory;
use App\Models\ChartOfAccount;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountService
{

    public function fetchAccountData(int|null $categoryID)
    {
        $data = ChartOfAccount::select(
            'id',
            'category_id',
            'code',
            'name',
        )
        ->where('company_id', config('context.selected_company_id'))
        ->whereNull('deleted_at');

        if ($categoryID) {
            $data->where('category_id', $categoryID);
        }

        $data = $data->get();

        return $data;
    }

    public function fetchAccountCategoryData()
    {
        $data = AccountCategory::select(
            'id',
            'name',
        )
        ->where('company_id', config('context.selected_company_id'))
        ->whereNull('deleted_at')
        ->get();

        return $data;
    }

    public function fetchAccountTableData(Request $request)
    {
        $data = ChartOfAccount::with(['category:id,name'])->select(
            'id',
            'category_id',
            'code',
            'name',
            'note',
        )
        ->where('company_id', config('context.selected_company_id'));

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        $data = $data->orderBy('code', 'asc')->paginate($request->input('per_page', 10));
        return $data;
    }

    public function storeAccount(Request $request)
    {
        $existingAccount = ChartOfAccount::where('company_id', config('context.selected_company_id'))
            ->where('code', $request->code)
            ->first();

        if ($existingAccount) {
            throw ValidationException::withMessages([
                'code' => 'Kode akun sudah digunakan. Silakan gunakan kode lain.',
            ]);
        }
        ChartOfAccount::create([
            'company_id' => config('context.selected_company_id'),
            'category_id' => $request->category_id, 
            'code' => $request->code,
            'name' => $request->name,
            'note' => $request->note,
            'is_deletable' => true,
        ]);
    }

    public function updateAccount(Request $request, int $id)
    {
        $account = ChartOfAccount::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'id' => ['Akun tidak ditemukan.'],
            ]);
        }

        $existingAccount = ChartOfAccount::where('company_id', config('context.selected_company_id'))
            ->where('code', $request->code)
            ->where('id', '!=', $id)
            ->first();

        if ($existingAccount) {
            throw ValidationException::withMessages([
                'code' => 'Kode akun sudah digunakan. Silakan gunakan kode lain.',
            ]);
        }

        $account->update([
            'category_id' => $request->category_id,
            'code' => $request->code,
            'name' => $request->name,
            'note' => $request->note,
        ]);
    }

    public function toggleAccountStatus(int $id)
    {
        $account = ChartOfAccount::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'id' => ['Akun tidak ditemukan.'],
            ]);
        }

        $account->update([
            'deleted_at' => $account->deleted_at ? null : now(),
        ]);
    }
}