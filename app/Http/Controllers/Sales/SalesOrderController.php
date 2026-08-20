<?php

namespace App\Http\Controllers\Sales;

use App\Enums\AccountCategoryEnum;
use App\Enums\PaymentTerm;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesOrderFormRequest;
use App\Services\Master\AccountService;
use App\Services\Master\ContactService;
use App\Services\InventoryService;
use App\Services\Sales\SalesOrderService;
use App\Services\Master\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SalesOrderController extends Controller
{
    private SalesOrderService $salesOrderService;
    private InventoryService $inventoryService;
    private WarehouseService $warehouseService;
    private ContactService $contactService;
    private AccountService $accountService;

    public function __construct(SalesOrderService $salesOrderService, InventoryService $inventoryService, WarehouseService $warehouseService, ContactService $contactService, AccountService $accountService)
    {
        $this->salesOrderService = $salesOrderService;
        $this->inventoryService = $inventoryService;
        $this->warehouseService = $warehouseService;
        $this->contactService = $contactService;
        $this->accountService = $accountService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'penjualan',
            'breadcrumb'     => [['label' => 'Pemesanan']],
            'status' => SalesOrderStatus::dropdownOptions(),
        ];
        return view('sales.sales-order.index', $data);
    }

    public function datatable(Request $request)
    {
        $data = $this->salesOrderService->fetchSalesOrderTableData($request);
        return response()->json($data);
    }

    public function create()
    {
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Pemesanan', 'url' => route('sales.sales_orders.index')],
                ['label' => 'Tambah'],
            ],
            'number' => $this->salesOrderService->generateSONumber(),
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'inventories' => $this->inventoryService->fetchInventoryStock(companyID:config('context.selected_company_id')),
            'warehouses' => $this->warehouseService->fetchWarehouseData(),
            'customers' => $this->contactService->fetchContactData('customer'),
            'salesPersons' => $this->contactService->fetchContactData('employee'),
            'cashBankAccounts' => $this->accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::CASH_BANK->value),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];
        return view('sales.sales-order.create', $data);
    }

    public function store(SalesOrderFormRequest $request)
    {
        try {
            $this->salesOrderService->storeSalesOrder($request);
            return response()->json(['redirect' => route('sales.sales_orders.index'), 'message' => 'Sales Order berhasil dibuat.']);
        } catch (\Exception $e) {
            Log::error('Error SalesOrderController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat Sales Order. Silakan coba lagi.'], 500);
        }
    }

    public function show(int $id)
    {
        $salesOrder = $this->salesOrderService->fetchSalesOrderByID($id);
        if (!$salesOrder) {
            abort(404, 'Sales Order tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('sales.sales_orders.index')],
                ['label' => 'Detail'],
            ],
            'salesOrder' => $salesOrder,
        ];
        return view('sales.sales-order.show', $data);
    }

    public function edit(int $id)
    {
        $salesOrder = $this->salesOrderService->fetchSalesOrderByID($id);
        if (!$salesOrder) {
            abort(404, 'Sales Order tidak ditemukan.');
        }

        $companyID = config('context.selected_company_id');

        $data = [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('sales.sales_orders.index')],
                ['label' => 'Edit'],
            ],
            'salesOrder' => $salesOrder,
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'inventories' => $this->inventoryService->fetchInventoryStock(companyID: config('context.selected_company_id')),
            'warehouses' => $this->warehouseService->fetchWarehouseData(),
            'customers' => $this->contactService->fetchContactData('customer'),
            'salesPersons' => $this->contactService->fetchContactData('employee'),
            'cashBankAccounts' => $this->accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::CASH_BANK->value),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];
        return view('sales.sales-order.edit', $data);
    }

    public function update(SalesOrderFormRequest $request, int $id)
    {
        try {
            $this->salesOrderService->updateSalesOrder($request, $id);
            return response()->json(['redirect' => route('sales.sales_orders.index'), 'message' => 'Sales Order berhasil diperbarui.']);
        } catch (\Exception $e) {
            Log::error('Error SalesOrderController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Sales Order. Silakan coba lagi.'], 500);
        }
    }
    
    public function open(int $id)
    {
        try {
            $this->salesOrderService->changeSalesOrderStatus($id, SalesOrderStatus::OPEN->value);
            return response()->json(['message' => 'Sales Order berhasil dibuka.']);
        } catch (\Exception $e) {
            Log::error('Error SalesOrderController@open: ' . $e->getMessage(), [
                'exception' => $e,
                'sales_order_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuka Sales Order. Silakan coba lagi.'], 500);
        }
    }

    public function close(int $id)
    {
        try {
            $this->salesOrderService->changeSalesOrderStatus($id, SalesOrderStatus::CLOSED->value);
            return response()->json(['message' => 'Sales Order berhasil ditutup.']);
        } catch (\Exception $e) {
            Log::error('Error SalesOrderController@close: ' . $e->getMessage(), [
                'exception' => $e,
                'sales_order_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menutup Sales Order. Silakan coba lagi.'], 500);
        }
    }

    public function cancel(int $id)
    {
        try {
            $this->salesOrderService->changeSalesOrderStatus($id, SalesOrderStatus::CANCELLED->value);
            return response()->json(['message' => 'Sales Order berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error SalesOrderController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'sales_order_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan Sales Order. Silakan coba lagi.'], 500);
        }
    }
}