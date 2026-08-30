<?php

namespace App\Services\Master;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{

    public function fetchProductData(int $companyID)
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
            ->where('company_id', $companyID)
            ->whereNull('deleted_at')
            ->get();

        return $data;
    }

    public function fetchProductDataWithUnitCost(int $companyID)
    {
        $data = Product::query()
            ->select([
                'products.id',
                'products.code',
                'products.name',
                'products.unit_id',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'units.symbol as unit_symbol',
                DB::raw('COALESCE(product_stocks.average_unit_cost, 0) as average_unit_cost'),
            ])
            ->join('warehouses', function ($join) {
                $join->whereColumn('warehouses.company_id', 'products.company_id');
            })
            ->join('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('product_stocks', function ($join) {
                $join->on('product_stocks.product_id', '=', 'products.id')
                    ->on('product_stocks.warehouse_id', '=', 'warehouses.id');
            })
            ->where('products.company_id', $companyID)
            ->whereNull('products.deleted_at')
            ->whereNull('warehouses.deleted_at')
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
                'description',
                'deleted_at'
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

    // public function fetchProductTransactionTableData(Request $request, int $companyID)
    // {
    //     $poQuery = DB::table('purchase_order_items', 'i')
    //         ->join('purchase_orders as po', 'po.id', '=', 'i.purchase_order_id')
    //         ->join('contacts as c', 'c.id', '=', 'po.supplier_id')
    //         ->select(
    //             'po.id as transaction_id',
    //             'po.number as transaction_number',
    //             'po.reference_number as transaction_reference_number',
    //             DB::raw("DATE_FORMAT(po.order_date, '%Y-%m-%d') as transaction_date"),
    //             'c.name as contact_name',
    //             'i.quantity as quantity',
    //             'i.unit_price as unit_price',
    //             DB::raw('(i.quantity * i.unit_price) as total_price'),
    //             DB::raw('"purchase_order" as transaction_type'),
    //         )
    //         ->where('po.company_id', $companyID)
    //         ->where('i.product_id', $request->product_id)
    //         ->limit($request->input('per_page', 10));

    //     $grQuery = DB::table('goods_receipt_items', 'i')
    //         ->join('goods_receipts as gr', 'gr.id', '=', 'i.goods_receipt_id')
    //         ->join('contacts as c', 'c.id', '=', 'gr.supplier_id')
    //         ->select(
    //             'gr.id as transaction_id',
    //             'gr.number as transaction_number',
    //             'gr.reference_number as transaction_reference_number',
    //             DB::raw("DATE_FORMAT(gr.receipt_date, '%Y-%m-%d') as transaction_date"),
    //             'c.name as contact_name',
    //             'i.received_quantity as quantity',
    //             'i.unit_price as unit_price',
    //             DB::raw('(i.received_quantity * i.unit_price) as total_price'),
    //             DB::raw('"goods_receipt" as transaction_type'),
    //         )
    //         ->where('gr.company_id', $companyID)
    //         ->where('i.product_id', $request->product_id)
    //         ->limit($request->input('per_page', 10));

    //     $piQuery = DB::table('purchase_invoice_items', 'i')
    //         ->join('purchase_invoices as pi', 'pi.id', '=', 'i.purchase_invoice_id')
    //         ->join('contacts as c', 'c.id', '=', 'pi.supplier_id')
    //         ->select(
    //             'pi.id as transaction_id',
    //             'pi.number as transaction_number',
    //             'pi.reference_number as transaction_reference_number',
    //             DB::raw("DATE_FORMAT(pi.invoice_date, '%Y-%m-%d') as transaction_date"),
    //             'c.name as contact_name',
    //             'i.quantity as quantity',
    //             'i.unit_price as unit_price',
    //             DB::raw('(i.quantity * i.unit_price) as total_price'),
    //             DB::raw('"purchase_invoice" as transaction_type'),
    //         )
    //         ->where('pi.company_id', $companyID)
    //         ->where('i.product_id', $request->product_id)
    //         ->limit($request->input('per_page', 10));

    //     $data = $poQuery->unionAll($grQuery)->unionAll($piQuery);
    //     $data = $data->orderBy('transaction_date', 'asc')
    //         ->paginate($request->input('per_page', 10));

    //     return $data;
    // }

    public function fetchProductTransactionTableData(Request $request, int $companyID)
    {
        $query = InventoryTransaction::with(['productBatch:id,batch_number'])->select(
            'id',
            'product_id',
            'product_batch_id',
            'note',
            'reference_type',
            'reference_id',
            'transaction_date',
            'quantity',
            'unit_cost',
            'total_cost',
            'direction'
        )
            ->where('company_id', $companyID)
            ->where('product_id', $request->product_id);

        if ($request->filled('product_batch_id')) {
            $query->where('product_batch_id', $request->product_batch_id);
        }

        $data = $query->orderBy('transaction_date', 'desc')
            ->paginate($request->input('per_page', 10));

        return $data;


        // return $data;
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
                'description',
            )
            ->where('company_id', config('context.selected_company_id'))
            ->where('id', $id);

        $data = $data->firstOrFail();

        return $data;
    }

    public function fetchProductBatchByID(int $id)
    {
        return ProductBatch::with([
            'productBatchStocks.warehouse:id,name',
            'goodsReceiptItem.goodsReceipt:id,number',
        ])
            ->where('id', $id)
            ->firstOrFail();
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

    public function fetchProductUnitData()
    {
        $data = DB::table('units')
            ->select(
                'id',
                'name',
                'symbol'
            )
            ->where('deleted_at', null)
            ->get();

        return $data;
    }

    public function generateBatchNumber(int $productID, int $companyID): string
    {
        $product = Product::where('id', $productID)
            ->where('company_id', $companyID)
            ->firstOrFail();

        $prefix = $product->batch_prefix . '-' . date('Ymd') . '-';
        $lastBatch = DB::table('product_batches')
            ->where('company_id', $companyID)
            ->where('product_id', $productID)
            ->where('batch_number', 'like', $prefix . '%')
            ->orderBy('batch_number', 'desc')
            ->first();

        if ($lastBatch) {
            $lastBatchNumber = (int) substr($lastBatch->batch_number, strrpos($lastBatch->batch_number, '-') + 1);
            $newBatchNumber = $lastBatchNumber + 1;
        } else {
            $newBatchNumber = 1;
        }

        return $prefix . str_pad($newBatchNumber, 4, '0', STR_PAD_LEFT);
    }
}
