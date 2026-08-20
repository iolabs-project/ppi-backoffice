<?php

namespace App\Http\Controllers\Sales;

use App\Enums\DeliveryOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\DeliveryOrderFormRequest;
use App\Services\Master\AccountService;
use App\Services\Sales\DeliveryOrderService;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\Request;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeliveryOrderController extends Controller
{
    private AccountService $accountService;
    private InventoryService $inventoryService;

    public function __construct(AccountService $accountService, InventoryService $inventoryService)
    {
        $this->accountService = $accountService;
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $data = [
            'currentPage' => 'penjualan.pengiriman',
            'breadcrumb'  => [
                ['label' => 'Pengiriman'],
            ],
            'status' => DeliveryOrderStatus::dropdownOptions(),
        ];
        return view('sales.delivery-order.index', $data);
    }

    public function datatable(Request $request, DeliveryOrderService $deliveryOrderService)
    {
        $data = $deliveryOrderService->fetchDeliveryOrderTableData($request);
        return response()->json($data);
    }

    public function store(DeliveryOrderFormRequest $request, DeliveryOrderService $deliveryOrderService)
    {
        try {
            $data = $deliveryOrderService->storeDeliveryOrder($request);

            return response()->json(['redirect' => route('sales.delivery_orders.edit', $data->id), 'message' => 'Delivery order berhasil dibuat.']);
        } catch (ValidationException $e) {
            Log::error('Error DeliveryOrderController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error DeliveryOrderController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat delivery order. Silakan coba lagi.'], 500);
        }
    }

    public function edit(DeliveryOrderService $deliveryOrderService, SalesOrderService $salesOrderService, int $id)
    {
        $deliveryOrder = $deliveryOrderService->fetchDeliveryOrderByID($id);
        if (!$deliveryOrder) {
            abort(404, 'Delivery order tidak ditemukan.');
        }

        $companyID = config('context.selected_company_id');

        $remainingSOItems = $salesOrderService->fetchSOItemsForDeliveryOrder($deliveryOrder->sales_order_id);
        // $availableBatches = $deliveryOrderService->fetchAvailableBatches(
        //     $deliveryOrder->warehouse_id,
        //     $remainingSOItems->pluck('product_id')->unique()->values()->all()
        // );
        $availableBatches = $this->inventoryService->fetchInventoryBatches(
            companyID: $deliveryOrder->company_id,
            productIDs: $remainingSOItems->pluck('product_id')->unique()->values()->all(),
            onlyAvailable: true
        )->map(function ($batch) {
            return [
                'id' => $batch->id,
                'product_id' => $batch->product_id,
                'batch_number' => $batch->batch_number,
                'available_quantity' => $batch->available_quantity,
                'unit_cost' => $batch->unit_cost,
            ];
        })->values();

        $data = [
            'currentPage' => 'penjualan.pengiriman',
            'breadcrumb'  => [
                ['label' => 'Pengiriman', 'url' => route('sales.delivery_orders.index')],
                ['label' => 'Edit'],
            ],
            'deliveryOrder' => $deliveryOrder,
            'remainingSOItems' => $remainingSOItems,
            'availableBatches' => $availableBatches,
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];
        return view('sales.delivery-order.edit', $data);
    }

    public function show(DeliveryOrderService $deliveryOrderService, int $id)
    {
        $deliveryOrder = $deliveryOrderService->fetchDeliveryOrderByID($id);
        if (!$deliveryOrder) {
            abort(404, 'Delivery order tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'penjualan.pengiriman',
            'breadcrumb'  => [
                ['label' => 'Pengiriman', 'url' => route('sales.delivery_orders.index')],
                ['label' => 'Detail'],
            ],
            'deliveryOrder' => $deliveryOrder,
        ];
        return view('sales.delivery-order.show', $data);
    }

    public function update(DeliveryOrderFormRequest $request, DeliveryOrderService $deliveryOrderService, int $id)
    {
        try {
            $deliveryOrderService->updateDeliveryOrder($request, $id);
            return response()->json(['redirect' => route('sales.delivery_orders.index'), 'message' => 'Pengiriman Barang berhasil diperbarui.']);
        } catch (ValidationException $e) {
            Log::error('Error DeliveryOrderController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error DeliveryOrderController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Pengiriman Barang. Silakan coba lagi.'], 500);
        }
    }

    public function cancel(Request $request, DeliveryOrderService $deliveryOrderService, int $id)
    {
        try {
            $deliveryOrderService->changeDeliveryOrderStatus($id, DeliveryOrderStatus::CANCELLED->value);
            return response()->json(['message' => 'Pengiriman Barang berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error DeliveryOrderController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan Pengiriman Barang. Silakan coba lagi.'], 500);
        }
    }
}
