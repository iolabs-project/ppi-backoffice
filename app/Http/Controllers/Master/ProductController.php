<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ProductFormRequest;
use App\Services\Master\ProductService;
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
    public function store(ProductFormRequest $request, ProductService $productService)
    {
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
    public function update(ProductFormRequest $request, ProductService $productService, int $id)
    {
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
