<?php

namespace App\Services\Master;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{

    public function fetchProductData()
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
            ->whereNull('deleted_at')
            ->get();

        return $data;
    }
    public function fetchProductDataWithStock()
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
            ->whereNull('deleted_at')
            ->get();

        return $data;
    }
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

    public function fetchProductTableData(Request $request)
    {
        $data = Product::with([
            'category:id,name',
            'unit:id,name,symbol',
        ])
            ->select(
                'id',
                'code',
                'name',
                'category_id',
                'unit_id',
                'minimum_stock',
                'inventory_account_id',
                'sales_account_id',
                'cogs_account_id'
            )
            ->where('company_id', config('context.selected_company_id'));

        if ($request->filled('search')) {
            $data->where(function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $data->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $data->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $data->whereNotNull('deleted_at');
            }
        }

        // return $data;
        $data = $data->orderBy('code', 'asc')
            ->paginate($request->input('per_page', 10));

        return $data;
    }

    public function fetchProductByID(int $id)
    {
        $data = Product::with([
            'category:id,name',
            'unit:id,name,symbol',
        ])
            ->select(
                'id',
                'code',
                'name',
                'category_id',
                'unit_id',
                'minimum_stock',
                'inventory_account_id',
                'sales_account_id',
                'cogs_account_id'
            )
            ->where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->first();

        return $data;
    }

    public function storeProduct(Request $request)
    {
        $checkExistingCode = Product::where('company_id', config('context.selected_company_id'))
            ->where('code', $request->code)
            ->exists();

        if ($checkExistingCode) {
            throw ValidationException::withMessages([
                'code' => 'Kode produk sudah digunakan. Silakan gunakan kode lain.',
            ]);
        }

        Product::create([
            'company_id' => config('context.selected_company_id'),
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'minimum_stock' => $request->minimum_stock,
            'inventory_account_id' => $request->inventory_account_id,
            'sales_account_id' => $request->sales_account_id,
            'cogs_account_id' => $request->cogs_account_id,
        ]);
    }

    public function updateProduct(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $checkExistingCode = Product::where('company_id', config('context.selected_company_id'))
            ->where('code', $request->code)
            ->where('id', '!=', $product->id)
            ->exists();

        if ($checkExistingCode) {
            throw ValidationException::withMessages([
                'code' => 'Kode produk sudah digunakan. Silakan gunakan kode lain.',
            ]);
        }

        $product->update([
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'minimum_stock' => $request->minimum_stock,
            'inventory_account_id' => $request->inventory_account_id,
            'sales_account_id' => $request->sales_account_id,
            'cogs_account_id' => $request->cogs_account_id,
        ]);
    }

    public function toggleProductStatus(int $id)
    {
        Product::where('id', $id)
            ->update([
                'deleted_at' => DB::raw('IF(deleted_at IS NULL, NOW(), NULL)'),
            ]);
    }

    public function fetchProductCategoryData()
    {
        $data = ProductCategory::where('company_id', config('context.selected_company_id'))
            ->select(
                'id',
                'name'
            )
            ->get();

        return $data;
    }

    public function storeProductCategory(Request $request)
    {
        ProductCategory::create([
            'company_id' => config('context.selected_company_id'),
            'name' => $request->name,
            'note' => $request->note,
        ]);
    }

    public function updateProductCategory(Request $request, int $id)
    {
        $productCategory = ProductCategory::where('company_id', config('context.selected_company_id'))
            ->where('id', $id)
            ->firstOrFail();

        $productCategory->update([
            'name' => $request->name,
            'note' => $request->note,
        ]);
    }
}
