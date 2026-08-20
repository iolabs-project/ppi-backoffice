<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\BilledBy;
use App\Enums\GoodsReceiptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\GoodsReceiptFormRequest;
use App\Services\Master\AccountService;
use App\Services\Purchasing\GoodsReceiptService;
use App\Services\Purchasing\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GoodsReceiptController extends Controller
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

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

    public function datatable(Request $request, GoodsReceiptService $goodsReceiptService)
    {
        $data = $goodsReceiptService->fetchGoodsReceiptTableData($request);
        return response()->json($data);
    }

    public function store(GoodsReceiptFormRequest $request, GoodsReceiptService $goodsReceiptService)
    {
        try {
            $data = $goodsReceiptService->storeGoodsReceipt($request);

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

    public function show(GoodsReceiptService $goodsReceiptService, int $id)
    {
        $data = [
            'currentPage'   => 'pembelian.penerimaan',
            'breadcrumb'    => [
                ['label' => 'Penerimaan Barang', 'url' => route('purchasings.goods_receipts.index')],
                ['label' => 'Detail'],
            ],
            'goodsReceipt'  => $goodsReceiptService->fetchGoodsReceiptByID($id),
        ];

        return view('purchasing.goods-receipt.show', $data);
    }

    public function edit(GoodsReceiptService $goodsReceiptService, PurchaseOrderService $purchaseOrderService, int $id)
    {
        $goodsReceipt = $goodsReceiptService->fetchGoodsReceiptByID($id);
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'   => 'pembelian.penerimaan',
            'breadcrumb'    => [
                ['label' => 'Penerimaan Barang', 'url' => route('purchasings.goods_receipts.index')],
                ['label' => 'Edit'],
            ],
            'goodsReceipt'  => $goodsReceipt,
            'remainingPOItems' => $purchaseOrderService->fetchPOItemsForGoodsReceipt($goodsReceipt->purchase_order_id),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];

        return view('purchasing.goods-receipt.edit', $data);
    }

    public function update(Request $request, GoodsReceiptService $goodsReceiptService, int $id)
    {
        try {
            $goodsReceiptService->updateGoodsReceipt($request, $id);
            return response()->json(['redirect' => route('purchasings.goods_receipts.index'), 'message' => 'Penerimaan barang berhasil diperbarui.']);
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

    public function cancel(Request $request, GoodsReceiptService $goodsReceiptService, int $id)
    {
        try {
            $goodsReceiptService->changeGoodsReceiptStatus($id, GoodsReceiptStatus::CANCELLED->value);
            return response()->json(['message' => 'Penerimaan barang berhasil dibatalkan.']);
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
