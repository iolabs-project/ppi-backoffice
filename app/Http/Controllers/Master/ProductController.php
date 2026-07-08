<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{

    public function index()
    {
        
    }

    public function datatable(Request $request, ProductService $productService)
    {
        try {
            $data = $productService->fetchProductTableData($request);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/ProductController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data produk',
            ], 500);
        }
    }
    public function store(Request $request, ProductService $productService)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:products,code,NULL,id,company_id,' . config('context.selected_company_id'),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'category_id' => 'required|exists:product_categories,id',
            'minimum_stock' => 'nullable|numeric|min:0',
            'inventory_account_id' => 'required|exists:chart_of_accounts,id',
            'sales_account_id' => 'required|exists:chart_of_accounts,id',
            'cogs_account_id' => 'required|exists:chart_of_accounts,id',
        ]);
        try {
            $productService->storeProduct($request);

            return response()->json([
                'message' => 'Produk berhasil dibuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Error ProductController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat produk',
            ], 500);
        }
    }
    public function update(Request $request, ProductService $productService, int $id)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:products,code,' . $id . ',id,company_id,' . config('context.selected_company_id'),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'category_id' => 'required|exists:product_categories,id',
            'minimum_stock' => 'nullable|numeric|min:0',
            'inventory_account_id' => 'required|exists:chart_of_accounts,id',
            'sales_account_id' => 'required|exists:chart_of_accounts,id',
            'cogs_account_id' => 'required|exists:chart_of_accounts,id',
        ]);
        try {
            $productService->updateProduct($request, $id);

            return response()->json([
                'message' => 'Produk berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error ProductController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui produk',
            ], 500);
        }
    }

    public function status(ProductService $productService, int $id)
    {
        try {
            $productService->toggleProductStatus($id);

            return response()->json([
                'message' => 'Status produk berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error ProductController@status: ' . $e->getMessage(), [
                'exception' => $e,
                'product_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui status produk',
            ], 500);
        }
    }
    public function options(Request $request, ProductService $productService)
    {
        try {
            $data = $productService->fetchOptionData($request);

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error ProductController@options: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data produk',
            ], 500);
        }
    }
}
