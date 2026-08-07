<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ProductFormRequest;
use App\Services\Master\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ErpDataService;
use App\Services\InventoryService;

class ProductController extends Controller
{

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

    public function transactionDatatable(Request $request, ProductService $productService) {
        try {
            $data = $productService->fetchProductTransactionTableData(request: $request, companyID: config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/ProductController@transactionDatatable: ' . $e->getMessage(), [
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
    public function show(ProductService $productService, InventoryService $inventoryService, int $id)
    {
        // $data = [
        //     'currentPage' => 'master',
        //     'breadcrumb'  => [['label' => 'Master Produk']],
        //     'product'     => $productService->fetchProductById($id),
        // ];

        // return view('master.product.show', $data);
        $produkList = ErpDataService::produk();
        $produk = collect($produkList)->firstWhere('kode', 'TPG-001');
        $product = $productService->fetchProductById($id);
        $stocks = $inventoryService->fetchGlobalInventoryStock(companyID: config('context.selected_company_id'), productIDs: [$id]);

        if (!$produk) abort(404);

        $stok = $produk['stok'] ?? 0;

        $stokPerGudang = [
            ['nama' => 'Unassigned',       'stok' => (int)round($stok * 0.34)],
            ['nama' => 'Gudang ' . ($produk['gudang'] ?? 'Bekasi'), 'stok' => (int)round($stok * 0.33)],
            ['nama' => 'Gudang Lain',      'stok' => $stok - (int)round($stok * 0.34) - (int)round($stok * 0.33)],
        ];

        $transaksiTerkini = [
            ['tanggal' => '26/05/2026', 'deskripsi' => 'Pengiriman Penjualan (SD/00001)', 'ref' => '',         'qty' => -1,  'harga' => $produk['hargaJual'], 'total' => $produk['hargaJual']],
            ['tanggal' => '22/05/2026', 'deskripsi' => 'Tagihan Penjualan (INV/00048)',   'ref' => 'INV/00048', 'qty' => -2,  'harga' => $produk['hargaJual'], 'total' => $produk['hargaJual'] * 2],
            ['tanggal' => '20/05/2026', 'deskripsi' => 'Pemesanan Pembelian (PI/00055)',  'ref' => 'PI/00055',  'qty' => 200, 'harga' => $produk['hargaBeli'], 'total' => $produk['hargaBeli'] * 200],
        ];

        $transferGudang = [
            ['tanggal' => '15/04/2026', 'nomor' => 'WT/00001', 'dari' => 'Unassigned',       'ke' => 'Gudang ' . ($produk['gudang'] ?? 'Bekasi'), 'qty' => 10],
            ['tanggal' => '16/04/2026', 'nomor' => 'WT/00002', 'dari' => 'Gudang ' . ($produk['gudang'] ?? 'Bekasi'), 'ke' => 'Gudang Lain',     'qty' => 5],
            ['tanggal' => '17/04/2026', 'nomor' => 'WT/00003', 'dari' => 'Gudang Lain',      'ke' => 'Unassigned',                                'qty' => 2],
        ];

        return view('master.product.show', [
            'currentPage'      => 'master',
            'breadcrumb'       => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => $produk['nama']],
            ],
            'produk'           => $produk,
            'product'          => $product,
            'stocks'           => $stocks,
            'receivedQty'    => $inventoryService->fetchQuantityReceivedByProductThisMonth(companyID: config('context.selected_company_id'), productID: $id),
            'soldQty'        => $inventoryService->fetchQuantitySoldByProductThisMonth(companyID: config('context.selected_company_id'), productID: $id),
            'stokPerGudang'    => $stokPerGudang,
            'transaksiTerkini' => $transaksiTerkini,
            'transferGudang'   => $transferGudang,
            'akunPembelian'    => '5-1000 HPP',
            'akunPenjualan'    => '4-1000 Pendapatan Penjualan',
            'akunInventori'    => '1-1300 Persediaan Barang',
        ]);
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
