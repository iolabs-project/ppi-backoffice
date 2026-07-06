<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function fetchGlobalInventoryStock()
    {
        $data = ProductBatch::with([
            'product:id,code,name,unit_id',
            'product.unit:id,name,symbol',
        ])
            ->select(
                'company_id',
                'product_id',
                'warehouse_id',
                // 'quantity',
                // 'reserved_quantity',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(reserved_quantity) as reserved_quantity'),
            )
            ->where('company_id', config('context.selected_company_id'))
            ->groupBy('product_id', 'company_id', 'warehouse_id')
            ->havingRaw('quantity - reserved_quantity > 0')
            ->get();

        return $data;
    }
}
