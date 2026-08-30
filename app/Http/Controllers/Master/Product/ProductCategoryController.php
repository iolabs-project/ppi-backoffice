<?php

namespace App\Http\Controllers\Master\Product;

use App\Http\Controllers\Controller;
use App\Services\Master\ProductService;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function store(Request $request, ProductService $productService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $productService->storeProductCategory($request);

            return response()->json([
                'message' => 'Kategori produk berhasil dibuat',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error ProductCategoryController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat kategori produk',
            ], 500);
        }
    }

    public function update(Request $request, ProductService $productService, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $productService->updateProductCategory($request, $id);

            return response()->json([
                'message' => 'Kategori produk berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error ProductCategoryController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui kategori produk',
            ], 500);
        }
    }
}
