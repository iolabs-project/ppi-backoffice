<?php

namespace App\Http\Controllers\Sales;

use App\Enums\DeliveryOrderStatus;
use App\Http\Controllers\Controller;
use App\Services\Sales\DeliveryOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeliveryOrderController extends Controller
{
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

    public function store(Request $request, DeliveryOrderService $deliveryOrderService)
    {
        try {
            $request->validate(
                [
                    'sales_order_id' => 'required|exists:sales_orders,id',
                ],
                [
                    'sales_order_id.required' => 'ID Sales Order harus diisi.',
                    'sales_order_id.exists' => 'Sales Order tidak ditemukan.',
                ]
            );

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

    public function edit(DeliveryOrderService $deliveryOrderService, int $id)
    {
        $deliveryOrder = $deliveryOrderService->fetchDeliveryOrderByID($id);
        if (!$deliveryOrder) {
            abort(404, 'Delivery order tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'penjualan.pengiriman',
            'breadcrumb'  => [
                ['label' => 'Pengiriman', 'url' => route('sales.delivery_orders.index')],
                ['label' => 'Edit'],
            ],
            'deliveryOrder' => $deliveryOrder,
            'remainingSOItems' => []
        ];
        return view('sales.delivery-order.edit', $data);
    }
}
