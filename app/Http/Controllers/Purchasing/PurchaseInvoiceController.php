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
            'currentPage'    => 'pembelian.tagihan',
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
            'currentPage' => 'pembelian.tagihan',
            'breadcrumb'  => [
                ['label' => 'Tagihan', 'url' => route('purchasings.purchase_invoices.index')],
                ['label' => 'Edit'],
            ],
            'purchaseInvoice' => $purchaseInvoice,
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'remainingGRItems' => $purchasingService->fetchGRItemsForPurchaseInvoice($purchaseInvoice->purchase_order_id),
        ];
        return view('purchasing.purchase-invoice.edit', $data);
    }

    public function update(Request $request, PurchasingService $purchasingService, int $id)
    {
        if ($request->input('status') !== PurchaseInvoiceStatus::DRAFT->value) {
            $request->validate(
                [
                    'reference_number' => 'nullable|string|max:50',
                    'invoice_date' => 'required|date',
                    'due_date' => 'required|date|after_or_equal:invoice_date',
                    'status' => 'required|in:open',
                    'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'other_cost' => 'nullable|numeric|min:0',
                    'down_payment_amount' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'details' => 'required|array|min:1',
                    'details.*.goods_receipt_item_id' => 'required|exists:goods_receipt_items,id',
                    'details.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
                    'details.*.product_id' => 'required|exists:products,id',
                    'details.*.quantity' => 'required|numeric|min:0',
                    'details.*.unit_price' => 'required|numeric|min:0',
                    'details.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
                ],
                [
                    'invoice_date.required' => 'Tanggal invoice harus diisi.',
                    'due_date.required' => 'Tanggal jatuh tempo harus diisi.',
                    'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice.',
                    'status.required' => 'Status invoice harus diisi.',
                    'details.required' => 'Daftar item invoice harus diisi.',
                    'details.*.goods_receipt_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item GR.',
                    'details.*.purchase_order_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item PO.',
                    'details.*.product_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item PO.',
                    'details.*.quantity.required' => 'Terdapat produk yang belum diisi jumlah. Silakan isi jumlah yang diharapkan untuk setiap produk.',
                    'details.*.unit_price.required' => 'Terdapat produk yang belum diisi harga satuannya. Silakan isi harga satuan untuk setiap produk.',
                    'details.*.discount_percentage.required' => 'Terdapat produk yang belum diisi persentase diskon. Silakan isi persentase diskon untuk setiap produk.',
                ]
            );
        }

        try {
            $purchasingService->updatePurchaseInvoice($request, $id);
            return response()->json(['redirect' => route('purchasings.purchase_invoices.index'), 'message' => 'Invoice Pembelian berhasil diperbarui.']);
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
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Invoice Pembelian. Silakan coba lagi.'], 500);
        }
    }

    public function cancel(PurchasingService $purchasingService, int $id)
    {
        try {
            $purchasingService->cancelPurchaseInvoice($id);
            return response()->json(['message' => 'Invoice Pembelian berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseInvoiceController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'purchase_invoice_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan Invoice Pembelian. Silakan coba lagi.'], 500);
        }
    }
}
