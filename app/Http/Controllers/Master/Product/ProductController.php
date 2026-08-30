<?php

namespace App\Http\Controllers\Master\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ProductFormRequest;
use App\Services\Master\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ErpDataService;
use App\Services\InventoryService;

class ProductController extends Controller
{
    private ProductService $productService;
    private InventoryService $inventoryService;

    public function __construct(ProductService $productService, InventoryService $inventoryService)
    {
        $this->productService = $productService;
        $this->inventoryService = $inventoryService;
    }
    public function datatable(Request $request)
    {
        try {
            $data = $this->productService->fetchProductTableData($request);

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

    public function transactionDatatable(Request $request)
    {
        try {
            $data = $this->productService->fetchProductTransactionTableData(request: $request, companyID: config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/ProductController@transactionDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data transaksi produk',
            ], 500);
        }
    }
    public function store(ProductFormRequest $request)
    {
        try {
            $this->productService->storeProduct($request);

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
    public function show(int $id)
    {
        $product = $this->productService->fetchProductById($id);
        $stocks = $this->inventoryService->fetchInventoryStock(companyID: config('context.selected_company_id'), productIDs: [$id]);
        $data = [
            'currentPage'      => 'master',
            'breadcrumb'       => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => 'Produk'],
            ],
            'product'          => $product,
            'stocks'           => $stocks,
            'receivedQty'    => $this->inventoryService->fetchQuantityReceivedByProductThisMonth(companyID: config('context.selected_company_id'), productID: $id),
            'soldQty'        => $this->inventoryService->fetchQuantitySoldByProductThisMonth(companyID: config('context.selected_company_id'), productID: $id),
            'categories'        => $this->productService->fetchProductCategoryData(),
            'units'             => $this->productService->fetchProductUnitData(),
        ];

        return view('master.product.show', $data);
    }
    public function update(ProductFormRequest $request, int $id)
    {
        try {
            $this->productService->updateProduct($request, $id);

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

    public function status(int $id)
    {
        try {
            $this->productService->toggleProductStatus($id);

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
    public function options(Request $request)
    {
        try {
            $data = $this->productService->fetchOptionData($request);

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
