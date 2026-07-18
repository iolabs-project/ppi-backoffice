<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\PaymentTerm;
use App\Enums\PurchaseInvoiceStatus;
use App\Http\Controllers\Controller;
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

    public function store(Request $request)
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

            $data = $this->purchaseInvoiceService->storePurchaseInvoice($request);

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

    public function edit(int $id)
    {
        $purchaseInvoice = $this->purchaseInvoiceService->fetchPurchaseInvoiceByID($id);
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
            'remainingGRItems' => $this->goodsReceiptService->fetchGRItemsForPurchaseInvoice($purchaseInvoice->purchase_order_id),
            'accounts' => $this->accountService->fetchAccountData(null),
        ];
        return view('purchasing.purchase-invoice.edit', $data);
    }

    public function update(Request $request, int $id)
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
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
                    'costs.*.description' => 'nullable|string|max:1000',
                    'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
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
                    'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
                    'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
                ]
            );
        }

        try {
            $this->purchaseInvoiceService->updatePurchaseInvoice($request, $id);
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

    public function cancel(int $id)
    {
        try {
            $this->purchaseInvoiceService->cancelPurchaseInvoice($id);
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
