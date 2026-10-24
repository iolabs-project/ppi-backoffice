<?php

namespace App\Http\Controllers\Master\Product;

use App\Http\Controllers\Controller;
use App\Services\Master\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ProductCategoryController extends Controller
{
    // Matches the unique(company_id, name) index, so duplicates get a message instead of a 500
    private function uniqueNameRule(): Unique
    {
        return Rule::unique('product_categories', 'name')
            ->where('company_id', config('context.selected_company_id'));
    }

    public function store(Request $request, ProductService $productService)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', $this->uniqueNameRule()],
            'note' => 'nullable|string|max:255',
        ], ['name.unique' => 'Nama kategori sudah digunakan.']);

        try {
            $productService->storeProductCategory($request);

            return response()->json([
                'message' => 'Kategori produk berhasil dibuat',
            ]);
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
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
            'name' => ['required', 'string', 'max:255', $this->uniqueNameRule()->ignore($id)],
            'note' => 'nullable|string|max:255',
        ], ['name.unique' => 'Nama kategori sudah digunakan.']);

        try {
            $productService->updateProductCategory($request, $id);

            return response()->json([
                'message' => 'Kategori produk berhasil diperbarui',
            ]);
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
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
