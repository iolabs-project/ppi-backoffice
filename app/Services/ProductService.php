<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function fetchOptionData(Request $request)
    {
        $data = Product::with([
            'unit:id,name,symbol',
        ])
            ->select(
                'id',
                'code',
                'name',
                'unit_id'
            )
            ->where('company_id', config('context.selected_company_id'))
            ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $data->where('category_id', $request->category_id);
        }

        // TODO: Refactor this to handle batches not just stock
        // if ($request->filled('with_stock') && $request->filled('warehouse_id')) {
        //     $warehouseID = $request->warehouse_id;
        //     $subQuery = DB::table('product_stocks')
        //         ->selectRaw('SUM(quantity - reserved_quantity) as available_quantity')
        //         ->where('product_stocks.warehouse_id', $warehouseID);

        //     $data->leftJoinSub($subQuery, 'sub', function ($join) {
        //         $join->on('products.id', '=', 'sub.product_id');
        //     })
        //         ->addSelect('sub.available_quantity as available_quantity');
        // }

        $data = $data->get();

        return $data;
    }
}
