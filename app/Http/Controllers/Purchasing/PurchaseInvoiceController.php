<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\PaymentTerm;
use App\Enums\PurchaseInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\PurchaseInvoiceFormRequest;
use App\Services\Master\AccountService;
use App\Services\Purchasing\GoodsReceiptService;
use App\Services\Purchasing\PurchaseInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceController extends Controller
{
    private AccountService $accountService;
    private PurchaseInvoiceService $purchaseInvoiceService;
    private GoodsReceiptService $goodsReceiptService;

    public function __construct(AccountService $accountService, PurchaseInvoiceService $purchaseInvoiceService, GoodsReceiptService $goodsReceiptService)
    {
        $this->accountService = $accountService;
        $this->purchaseInvoiceService = $purchaseInvoiceService;
        $this->goodsReceiptService = $goodsReceiptService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'pembelian.tagihan',
            'breadcrumb'     => [['label' => 'Tagihan']],
            'status' => PurchaseInvoiceStatus::dropdownOptions(),
        ];
        return view('purchasing.purchase-invoice.index', $data);
    }

    public function datatable(Request $request)
    {
        $data = $this->purchaseInvoiceService->fetchPurchaseInvoiceTableData($request);
        return response()->json($data);
    }

    public function store(PurchaseInvoiceFormRequest $request)
    {
        try {
            $data = $this->purchaseInvoiceService->storePurchaseInvoice($request);

            return response()->json(['redirect' => route('purchasings.purchase_invoices.edit', $data->id), 'message' => 'Tagihan pembelian berhasil dibuat.']);
        } catch (ValidationException $e) {
            Log::error('Error PurchaseInvoiceController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error PurchaseInvoiceController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat tagihan pembelian. Silakan coba lagi.'], 500);
        }
    }

    public function show(PurchaseInvoiceService $purchaseInvoiceService, int $id)
    {
        $data = [
            'currentPage'   => 'pembelian.tagihan',
            'breadcrumb'    => [
                ['label' => 'Tagihan', 'url' => route('purchasings.purchase_invoices.index')],
                ['label' => 'Detail'],
            ],
            'purchaseInvoice'  => $purchaseInvoiceService->fetchPurchaseInvoiceByID($id),
        ];

        return view('purchasing.purchase-invoice.show', $data);
    }

    public function edit(int $id)
    {
        $purchaseInvoice = $this->purchaseInvoiceService->fetchPurchaseInvoiceByID($id);
        if (!$purchaseInvoice) {
            abort(404, 'Tagihan pembelian tidak ditemukan.');
        }
        $companyID = config('context.selected_company_id');

        $data = [
            'currentPage' => 'pembelian.tagihan',
            'breadcrumb'  => [
                ['label' => 'Tagihan', 'url' => route('purchasings.purchase_invoices.index')],
                ['label' => 'Edit'],
            ],
            'purchaseInvoice' => $purchaseInvoice,
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'remainingGRItems' => $this->goodsReceiptService->fetchGRItemsForPurchaseInvoice($purchaseInvoice->purchase_order_id),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];
        return view('purchasing.purchase-invoice.edit', $data);
    }

    public function update(PurchaseInvoiceFormRequest $request, int $id)
    {
        try {
            $this->purchaseInvoiceService->updatePurchaseInvoice($request, $id);
            return response()->json(['redirect' => route('purchasings.purchase_invoices.index'), 'message' => 'Tagihan pembelian berhasil diperbarui.']);
        } catch (ValidationException $e) {
            Log::error('Error PurchaseInvoiceController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error PurchaseInvoiceController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Tagihan pembelian. Silakan coba lagi.'], 500);
        }
    }

    public function cancel(int $id)
    {
        try {
            $this->purchaseInvoiceService->cancelPurchaseInvoice($id);
            return response()->json(['message' => 'Tagihan pembelian berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseInvoiceController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'purchase_invoice_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan Tagihan pembelian. Silakan coba lagi.'], 500);
        }
    }
}
