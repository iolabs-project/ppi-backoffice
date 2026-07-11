<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\GoodsReceiptStatus;
use App\Http\Controllers\Controller;
use App\Services\PurchasingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $data = [
            'currentPage' => 'pembelian.penerimaan',
            'breadcrumb'  => [
                ['label' => 'Penerimaan Barang'],
            ],
            'status' => GoodsReceiptStatus::dropdownOptions(),
        ];
        return view('purchasing.goods-receipt.index', $data);
    }

    public function datatable(Request $request, PurchasingService $purchasingService)
    {
        $data = $purchasingService->fetchGoodsReceiptTableData($request);
        return response()->json($data);
    }

    public function store(Request $request, PurchasingService $purchasingService)
    {
        try {
            $request->validate(
                [
                    'purchase_order_id' => 'required|exists:purchase_orders,id',
                ],
                [
                    'purchase_order_id.required' => 'ID Purchase Order harus diisi.',
                    'purchase_order_id.exists' => 'Purchase Order tidak ditemukan.',
                ]
            );

            $data = $purchasingService->storeGoodsReceipt($request);

            return response()->json(['redirect' => route('purchasings.goods_receipts.edit', $data->id), 'message' => 'Goods receipt berhasil dibuat.']);
        } catch (ValidationException $e) {
            Log::error('Error GoodsReceiptController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error GoodsReceiptController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat penerimaan barang. Silakan coba lagi.'], 500);
        }
    }

    public function show(PurchasingService $purchasingService, int $id)
    {
        $data = [
            'currentPage'   => 'pembelian.penerimaan',
            'breadcrumb'    => [
                ['label' => 'Penerimaan Barang', 'url' => route('purchasings.goods_receipts.index')],
                ['label' => 'Detail'],
            ],
            'goodsReceipt'  => $purchasingService->fetchGoodsReceiptByID($id),
        ];

        return view('purchasing.goods-receipt.show', $data);
    }

    public function edit(PurchasingService $purchasingService, int $id)
    {
        $goodsReceipt = $purchasingService->fetchGoodsReceiptByID($id);
        $data = [
            'currentPage'   => 'pembelian.penerimaan',
            'breadcrumb'    => [
                ['label' => 'Penerimaan Barang', 'url' => route('purchasings.goods_receipts.index')],
                ['label' => 'Edit'],
            ],
            'goodsReceipt'  => $goodsReceipt,
            'remainingPOItems' => $purchasingService->fetchPOItemsForGoodsReceipt($goodsReceipt->purchase_order_id),
        ];

        return view('purchasing.goods-receipt.edit', $data);
    }

    public function update(Request $request, PurchasingService $purchasingService, int $id)
    {
        if ($request->input('status') !== GoodsReceiptStatus::DRAFT->value) {
            $request->validate(
                [
                    'reference_number' => 'nullable|string|max:50',
                    'receipt_date' => 'required|date',
                    'status' => 'required|in:draft,finished',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'transport_cost' => 'nullable|numeric|min:0',
                    'other_cost' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'details' => 'required|array|min:1',
                    'details.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
                    'details.*.product_id' => 'required|exists:products,id',
                    'details.*.batch_number' => 'required|string|max:50',
                    'details.*.received_quantity' => 'required|numeric|min:0',
                    'details.*.expected_quantity' => 'nullable|numeric|min:0',
                    'details.*.unit_price' => 'required|numeric|min:0',
                    'details.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
                    'costs.*.description' => 'nullable|string|max:1000',
                    'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
                ],
                [
                    'receipt_date.required' => 'Tanggal penerimaan harus diisi.',
                    'status.required' => 'Status penerimaan harus diisi.',
                    'details.required' => 'Daftar item penerimaan harus diisi.',
                    'details.*.purchase_order_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item PO.',
                    'details.*.product_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item PO.',
                    'details.*.batch_number.required' => 'Terdapat produk yang belum diisi nomor batch. Silakan isi nomor batch untuk setiap produk.',
                    'details.*.expected_quantity.required' => 'Terdapat produk yang belum diisi jumlah yang diharapkan. Silakan isi jumlah yang diharapkan untuk setiap produk.',
                    'details.*.received_quantity.required' => 'Terdapat produk yang belum diisi jumlah penerimaannya. Silakan isi jumlah penerimaan untuk setiap produk.',
                    'details.*.unit_price.required' => 'Terdapat produk yang belum diisi harga satuannya. Silakan isi harga satuan untuk setiap produk.',
                    'details.*.discount_percentage.required' => 'Terdapat produk yang belum diisi persentase diskon. Silakan isi persentase diskon untuk setiap produk.',
                    'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
                    'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
                ]
            );
        }

        try {
            $purchasingService->updateGoodsReceipt($request, $id);
            return response()->json(['redirect' => route('purchasings.goods_receipts.index'), 'message' => 'Goods Receipt berhasil diperbarui.']);
        } catch (ValidationException $e) {
            Log::error('Error GoodsReceiptController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error GoodsReceiptController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Penerimaan Barang. Silakan coba lagi.'], 500);
        }
    }

    public function cancel(Request $request, PurchasingService $purchasingService, int $id)
    {
        try {
            $purchasingService->changeGoodsReceiptStatus($id, GoodsReceiptStatus::CANCELLED->value);
            return response()->json(['message' => 'Goods Receipt berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error GoodsReceiptController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan Penerimaan Barang. Silakan coba lagi.'], 500);
        }
    }
}
