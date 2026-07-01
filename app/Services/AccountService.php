<?php

namespace App\Services;

use App\Models\AccountCategory;
use App\Models\ChartOfAccount;
use App\Models\Warehouse;
use Illuminate\Http\Request;

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
}