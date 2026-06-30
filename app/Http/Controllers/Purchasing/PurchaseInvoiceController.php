<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\PaymentTerm;
use App\Enums\PurchaseInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Services\PurchasingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        $data = [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [['label' => 'Tagihan']],
            'status' => PurchaseInvoiceStatus::dropdownOptions(),
        ];
        return view('purchasing.purchase-invoice.index', $data);
    }

    public function datatable(Request $request, PurchasingService $purchasingService)
    {
        $data = $purchasingService->fetchPurchaseInvoiceTableData($request);
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

            $data = $purchasingService->storePurchaseInvoice($request);

            return response()->json(['redirect' => route('purchasings.purchase_invoices.edit', $data->id), 'message' => 'Purchase invoice berhasil dibuat.']);
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

    public function edit(PurchasingService $purchasingService, int $id)
    {
        $purchaseInvoice = $purchasingService->fetchPurchaseInvoiceByID($id);
        if (!$purchaseInvoice) {
            abort(404, 'Tagihan pembelian tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Tagihan', 'url' => route('purchasings.purchase_invoices.index')],
                ['label' => 'Edit'],
            ],
            'purchaseInvoice' => $purchaseInvoice,
            'paymentTerms' => PaymentTerm::dropdownOptions()
        ];
        return view('purchasing.purchase-invoice.edit', $data);
    }
}
