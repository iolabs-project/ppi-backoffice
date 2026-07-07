<?php

namespace App\Http\Controllers\Sales;

use App\Enums\AccountCategory;
use App\Enums\PaymentTerm;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Services\AccountService;
use App\Services\ContactService;
use App\Services\InventoryService;
use App\Services\Sales\SalesOrderService;
use App\Services\WarehouseService;
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
        $data = [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Pemesanan', 'url' => route('sales.sales_orders.index')],
                ['label' => 'Tambah'],
            ],
            'number' => $this->salesOrderService->generateSONumber(),
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'inventories' => $this->inventoryService->fetchGlobalInventoryStock(),
            'warehouses' => $this->warehouseService->fetchWarehouseData(),
            'customers' => $this->contactService->fetchContactData('customer'),
            'salesPersons' => $this->contactService->fetchContactData('employee'),
            'cashBankAccounts' => $this->accountService->fetchAccountData(AccountCategory::CASH_BANK->value),
        ];
        return view('sales.sales-order.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->input('status') === SalesOrderStatus::OPEN->value) {
            $request->validate(
                [
                    'customer_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'sales_person_id' => 'nullable|exists:contacts,id',
                    'number' => 'required|string|max:50|unique:sales_orders,number',
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'tax_amount' => 'nullable|numeric|min:0',
                    'shipping_charge' => 'nullable|numeric|min:0',
                    'other_charge' => 'nullable|numeric|min:0',
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
                ],
                [
                    'customer_id.required' => 'Customer harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'number.required' => 'Nomor SO harus diisi.',
                    'number.unique' => 'Nomor SO sudah digunakan. Silakan gunakan nomor lain.',
                    'order_date.required' => 'Tanggal SO harus diisi.',
                    'payment_terms.required' => 'Syarat pembayaran harus dipilih.',
                    'details.required' => 'Daftar item tidak boleh kosong.',
                    'details.*.product_id.required' => 'Produk harus dipilih untuk setiap item.',
                    'details.*.quantity.required' => 'Kuantitas harus diisi untuk setiap item.',
                    'details.*.unit_price.required' => 'Harga satuan harus diisi untuk setiap item.',
                ]
            );
        } else if ($request->input('status') === SalesOrderStatus::DRAFT->value) {
            $request->validate(
                [
                    'customer_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'number' => 'required|string|max:50|unique:sales_orders,number',
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'shipping_charge' => 'nullable|numeric|min:0',
                    'other_charge' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'status' => 'required|in:draft,open',
                ],
                [
                    'customer_id.required' => 'Customer harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'number.required' => 'Nomor SO harus diisi.',
                    'number.unique' => 'Nomor SO sudah digunakan. Silakan gunakan nomor lain.',
                    'order_date.required' => 'Tanggal SO harus diisi.',
                ]
            );
        } else {
            return response()->json(['message' => "Status tidak valid. Harus berupa draft atau open."], 400);
        }

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

        $data = [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('sales.sales_orders.index')],
                ['label' => 'Edit'],
            ],
            'salesOrder' => $salesOrder,
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'inventories' => $this->inventoryService->fetchGlobalInventoryStock(),
            'warehouses' => $this->warehouseService->fetchWarehouseData(),
            'customers' => $this->contactService->fetchContactData('customer'),
            'salesPersons' => $this->contactService->fetchContactData('employee'),
            'cashBankAccounts' => $this->accountService->fetchAccountData(AccountCategory::CASH_BANK->value),
        ];
        return view('sales.sales-order.edit', $data);
    }

    public function update(Request $request, int $id)
    {
        if ($request->input('status') === SalesOrderStatus::OPEN->value) {
            $request->validate(
                [
                    'customer_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'sales_person_id' => 'nullable|exists:contacts,id',
                    'number' => ['required', 'string', 'max:50', Rule::unique('sales_orders', 'number')->ignore($id)],
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'tax_amount' => 'nullable|numeric|min:0',
                    'shipping_charge' => 'nullable|numeric|min:0',
                    'other_charge' => 'nullable|numeric|min:0',
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
                ],
                [
                    'customer_id.required' => 'Customer harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'number.required' => 'Nomor SO harus diisi.',
                    'number.unique' => 'Nomor SO sudah digunakan. Silakan gunakan nomor lain.',
                    'order_date.required' => 'Tanggal SO harus diisi.',
                    'payment_terms.required' => 'Syarat pembayaran harus dipilih.',
                    'details.required' => 'Daftar item tidak boleh kosong.',
                    'details.*.product_id.required' => 'Produk harus dipilih untuk setiap item.',
                    'details.*.quantity.required' => 'Kuantitas harus diisi untuk setiap item.',
                    'details.*.unit_price.required' => 'Harga satuan harus diisi untuk setiap item.',
                ]
            );
        } else if ($request->input('status') === SalesOrderStatus::DRAFT->value) {
            $request->validate(
                [
                    'customer_id' => 'required|exists:contacts,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                    'number' => ['required', 'string', 'max:50', Rule::unique('sales_orders', 'number')->ignore($id)],
                    'reference_number' => 'nullable|string|max:50',
                    'order_date' => 'required|date',
                    'due_date' => 'nullable|date',
                    'discount_percentage' => 'nullable|numeric|min:0',
                    'tax_percentage' => 'nullable|numeric|min:0',
                    'shipping_charge' => 'nullable|numeric|min:0',
                    'other_charge' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'status' => 'required|in:draft,open',
                ],
                [
                    'customer_id.required' => 'Customer harus dipilih.',
                    'warehouse_id.required' => 'Gudang harus dipilih.',
                    'number.required' => 'Nomor SO harus diisi.',
                    'number.unique' => 'Nomor SO sudah digunakan. Silakan gunakan nomor lain.',
                    'order_date.required' => 'Tanggal SO harus diisi.',
                ]
            );
        } else {
            return response()->json(['message' => "Status tidak valid. Harus berupa draft atau open."], 400);
        }

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
            return response()->json(['message' => 'Status Sales Order berhasil diubah menjadi "Open"']);
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
            return response()->json(['message' => 'Status Sales Order berhasil diubah menjadi "Closed"']);
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
            return response()->json(['message' => 'Status Sales Order berhasil diubah menjadi "Cancelled"']);
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
