<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PurchaseOrder;

class PurchasingService
{
    public function generatePONumber(): string
    {
        $prefix = 'PO';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');
    
        $counter = PurchaseOrder::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }
}