<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\AccountCategoryEnum;
use App\Enums\BilledBy;
use App\Enums\PaymentTerm;
use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchasing\PurchaseOrderFormRequest;
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
        $companyID = config('context.selected_company_id');
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
            'cashBankAccounts' => $this->accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::CASH_BANK->value),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
            'billedByOptions' => BilledBy::dropdownOptions(),
        ];
        return view('purchasing.purchase-order.create', $data);
    }

    public function store(PurchaseOrderFormRequest $request)
    {
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

        $companyID = config('context.selected_company_id');

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
            'cashBankAccounts' => $this->accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::CASH_BANK->value),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
            'billedByOptions' => BilledBy::dropdownOptions(),
        ];
        return view('purchasing.purchase-order.edit', $data);
    }

    public function update(PurchaseOrderFormRequest $request, int $id)
    {
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
