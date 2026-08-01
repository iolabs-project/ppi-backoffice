<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\AccountCategory;
use App\Enums\BilledBy;
use App\Enums\PaymentTerm;
use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Services\Master\AccountService;
use App\Services\Master\ContactService;
use App\Services\Master\ProductService;
use App\Services\Purchasing\PurchaseOrderService;
use App\Services\Master\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    private PurchaseOrderService $purchaseOrderService;
    private ProductService $productService;
    private WarehouseService $warehouseService;
    private ContactService $contactService;
    private AccountService $accountService;

    public function __construct(PurchaseOrderService $purchaseOrderService, ProductService $productService, WarehouseService $warehouseService, ContactService $contactService, AccountService $accountService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
        $this->productService = $productService;
        $this->warehouseService = $warehouseService;
        $this->contactService = $contactService;
        $this->accountService = $accountService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [['label' => 'Pemesanan']],
            'status' => PurchaseOrderStatus::dropdownOptions(),
        ];
        return view('purchasing.purchase-order.index', $data);
    }

    public function datatable(Request $request)
    {
        $data = $this->purchaseOrderService->fetchPurchaseOrderTableData($request);
        return response()->json($data);
    }

    public function create()
    {
        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pemesanan', 'url' => route('purchasings.purchase_orders.index')],
                ['label' => 'Tambah'],
            ],
            'number' => $this->purchaseOrderService->generatePONumber(),
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'products' => $this->productService->fetchProductData(),
            'warehouses' => $this->warehouseService->fetchWarehouseData(),
            'suppliers' => $this->contactService->fetchContactData('supplier'),
            'cashBankAccounts' => $this->accountService->fetchAccountData(AccountCategory::CASH_BANK->value),
            'accounts' => $this->accountService->fetchAccountData(null),
            'billedByOptions' => BilledBy::dropdownOptions(),
        ];
        return view('purchasing.purchase-order.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->input('status') === PurchaseOrderStatus::OPEN->value) {
            $request->validate(
                [
                    'supplier_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'number' => 'required|string|max:50|unique:purchase_orders,number',
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'tax_amount' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'down_payment_amount' => 'nullable|numeric|min:0',
                    'down_payment_account_id' => 'nullable|exists:chart_of_accounts,id',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'status' => 'required|in:draft,open',
                    'details' => 'required|array|min:1',
                    'details.*.product_id' => 'required|exists:products,id',
                    'details.*.quantity' => 'required|numeric|min:1',
                    'details.*.unit_price' => 'required|numeric|min:0',
                    'details.*.discount_percentage' => 'nullable|numeric|min:0',
                    'details.*.discount_amount' => 'nullable|numeric|min:0',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
                    'costs.*.description' => 'nullable|string|max:1000',
                    'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
                    'costs.*.billed_by' => 'required_with:costs.*.amount|in:supplier,third_party,internal',
                    'costs.*.is_inventory_cost' => 'nullable|boolean',
                ],
                [
                    'supplier_id.required' => 'Supplier harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'number.required' => 'Nomor PO harus diisi.',
                    'number.unique' => 'Nomor PO sudah digunakan. Silakan gunakan nomor lain.',
                    'order_date.required' => 'Tanggal PO harus diisi.',
                    'payment_terms.required' => 'Syarat pembayaran harus dipilih.',
                    'details.required' => 'Daftar item tidak boleh kosong.',
                    'details.*.product_id.required' => 'Produk harus dipilih untuk setiap item.',
                    'details.*.quantity.required' => 'Kuantitas harus diisi untuk setiap item.',
                    'details.*.unit_price.required' => 'Harga satuan harus diisi untuk setiap item.',
                    'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
                    'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
                    'costs.*.billed_by.required_with' => 'Pihak yang menagih harus dipilih jika jumlah biaya diisi.',
                ]
            );
        } else if ($request->input('status') === PurchaseOrderStatus::DRAFT->value) {
            $request->validate(
                [
                    'supplier_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'number' => 'required|string|max:50|unique:purchase_orders,number',
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'status' => 'required|in:draft,open',
                ],
                [
                    'supplier_id.required' => 'Supplier harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'number.required' => 'Nomor PO harus diisi.',
                    'number.unique' => 'Nomor PO sudah digunakan. Silakan gunakan nomor lain.',
                    'order_date.required' => 'Tanggal PO harus diisi.',
                ]
            );
        } else {
            return response()->json(['message' => "Status tidak valid. Harus berupa draft atau open."], 400);
        }

        try {
            $this->purchaseOrderService->storePurchaseOrder($request);
            return response()->json(['redirect' => route('purchasings.purchase_orders.index'), 'message' => 'Purchase Order berhasil dibuat.']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseOrderController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat Purchase Order. Silakan coba lagi.'], 500);
        }
    }

    public function show(int $id)
    {
        $purchaseOrder = $this->purchaseOrderService->fetchPurchaseOrderByID($id);
        if (!$purchaseOrder) {
            abort(404, 'Purchase Order tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pemesanan', 'url' => route('purchasings.purchase_orders.index')],
                ['label' => 'Detail'],
            ],
            'purchaseOrder' => $purchaseOrder,
        ];
        return view('purchasing.purchase-order.show', $data);
    }

    public function edit(int $id)
    {
        $purchaseOrder = $this->purchaseOrderService->fetchPurchaseOrderByID($id);
        if (!$purchaseOrder) {
            abort(404, 'Purchase Order tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pemesanan', 'url' => route('purchasings.purchase_orders.index')],
                ['label' => 'Edit'],
            ],
            'purchaseOrder' => $purchaseOrder,
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'products' => $this->productService->fetchProductData(),
            'warehouses' => $this->warehouseService->fetchWarehouseData(),
            'suppliers' => $this->contactService->fetchContactData('supplier'),
            'cashBankAccounts' => $this->accountService->fetchAccountData(AccountCategory::CASH_BANK->value),
            'accounts' => $this->accountService->fetchAccountData(null),
            'billedByOptions' => BilledBy::dropdownOptions(),
        ];
        return view('purchasing.purchase-order.edit', $data);
    }

    public function update(Request $request, int $id)
    {
        if ($request->input('status') === PurchaseOrderStatus::OPEN->value) {
            $request->validate(
                [
                    'supplier_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'tax_amount' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'down_payment_amount' => 'nullable|numeric|min:0',
                    'down_payment_account_id' => 'nullable|exists:chart_of_accounts,id',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'status' => 'required|in:draft,open',
                    'details' => 'required|array|min:1',
                    'details.*.product_id' => 'required|exists:products,id',
                    'details.*.quantity' => 'required|numeric|min:1',
                    'details.*.unit_price' => 'required|numeric|min:0',
                    'details.*.discount_percentage' => 'nullable|numeric|min:0',
                    'details.*.discount_amount' => 'nullable|numeric|min:0',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
                    'costs.*.description' => 'nullable|string|max:1000',
                    'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
                    'costs.*.billed_by' => 'required_with:costs.*.amount|in:supplier,third_party,internal',
                    'costs.*.is_inventory_cost' => 'nullable|boolean',
                ],
                [
                    'supplier_id.required' => 'Supplier harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'order_date.required' => 'Tanggal PO harus diisi.',
                    'payment_terms.required' => 'Syarat pembayaran harus dipilih.',
                    'details.required' => 'Daftar item tidak boleh kosong.',
                    'details.*.product_id.required' => 'Produk harus dipilih untuk setiap item.',
                    'details.*.quantity.required' => 'Kuantitas harus diisi untuk setiap item.',
                    'details.*.unit_price.required' => 'Harga satuan harus diisi untuk setiap item.',
                    'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
                    'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
                    'costs.*.billed_by.required_with' => 'Pihak yang menagih harus dipilih jika jumlah biaya diisi.',
                ]
            );
        } else if ($request->input('status') === PurchaseOrderStatus::DRAFT->value) {
            $request->validate(
                [
                    'supplier_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'status' => 'required|in:draft,open',
                ],
                [
                    'supplier_id.required' => 'Supplier harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'order_date.required' => 'Tanggal PO harus diisi.',
                ]
            );
        } else {
            return response()->json(['message' => "Status tidak valid. Harus berupa draft atau open."], 400);
        }

        try {
            $this->purchaseOrderService->updatePurchaseOrder($request, $id);
            return response()->json(['redirect' => route('purchasings.purchase_orders.index'), 'message' => 'Purchase Order berhasil diperbarui.']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseOrderController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Purchase Order. Silakan coba lagi.'], 500);
        }
    }

    public function open(int $id)
    {
        try {
            $this->purchaseOrderService->changePurchaseOrderStatus($id, PurchaseOrderStatus::OPEN->value);
            return response()->json(['message' => 'Status Purchase Order berhasil diubah menjadi "Open"']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseOrderController@open: ' . $e->getMessage(), [
                'exception' => $e,
                'purchase_order_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuka Purchase Order. Silakan coba lagi.'], 500);
        }
    }

    public function close(int $id)
    {
        try {
            $this->purchaseOrderService->changePurchaseOrderStatus($id, PurchaseOrderStatus::CLOSED->value);
            return response()->json(['message' => 'Status Purchase Order berhasil diubah menjadi "Closed"']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseOrderController@close: ' . $e->getMessage(), [
                'exception' => $e,
                'purchase_order_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menutup Purchase Order. Silakan coba lagi.'], 500);
        }
    }

    public function cancel(int $id)
    {
        try {
            $this->purchaseOrderService->changePurchaseOrderStatus($id, PurchaseOrderStatus::CANCELLED->value);
            return response()->json(['message' => 'Status Purchase Order berhasil diubah menjadi "Cancelled"']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseOrderController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'purchase_order_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan Purchase Order. Silakan coba lagi.'], 500);
        }
    }
}
