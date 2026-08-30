<?php

namespace App\Http\Controllers\Master\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductBatchStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductOptionController extends Controller
{
    public function batches(Request $request)
    {
        try {
            $data = ProductBatchStock::with([
                'productBatch:id,company_id,product_id,batch_number,initial_quantity,unit_cost',
                'productBatch.product:id,code,name,unit_id',
                'productBatch.product.unit:id,name,symbol',
            ])
                ->whereHas('productBatch', function ($query) use ($request) {
                    $query->where('company_id', config('context.selected_company_id'))
                        ->whereHas('product', function ($query) {
                            $query->whereNull('deleted_at');
                        });
                })
                ->when($request->filled('warehouse_id'), function ($query) use ($request) {
                    $query->where('warehouse_id', $request->warehouse_id);
                })
                ->when($request->filled('exclude_batch_ids'), function ($query) use ($request) {
                    $query->whereNotIn('product_batch_id', (array) $request->input('exclude_batch_ids'));
                })
                ->when($request->boolean('only_available'), function ($query) {
                    $query->whereColumn('quantity', '>', 'reserved_quantity');
                })
                ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->whereHas('productBatch.product', function ($query) use ($request) {
                            $query->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('code', 'like', '%' . $request->search . '%');
                        })
                        ->orWhereHas('productBatch', function ($query) use ($request) {
                            $query->where('batch_number', 'like', '%' . $request->search . '%');
                        });
                    });
                })
                ->orderBy('id', 'asc')
                ->paginate($request->input('per_page', 20))
                ->through(fn(ProductBatchStock $stock) => [
                    'id' => $stock->product_batch_id,
                    'product_id' => $stock->productBatch->product_id,
                    'product' => $stock->productBatch->product,
                    'batch_number' => $stock->productBatch->batch_number,
                    'quantity' => $stock->quantity,
                    'unit_cost' => $stock->productBatch->unit_cost,
                ]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ProductOptionController@batches: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data batch produk',
            ], 500);
        }
    }
}
