<?php

namespace App\Http\Controllers\Sales;

use App\Enums\DeliveryOrderStatus;
use App\Http\Controllers\Controller;
use App\Services\Master\AccountService;
use App\Services\Sales\DeliveryOrderService;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeliveryOrderController extends Controller
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
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

    public function edit(DeliveryOrderService $deliveryOrderService, SalesOrderService $salesOrderService, int $id)
    {
        $deliveryOrder = $deliveryOrderService->fetchDeliveryOrderByID($id);
        if (!$deliveryOrder) {
            abort(404, 'Delivery order tidak ditemukan.');
        }

        $remainingSOItems = $salesOrderService->fetchSOItemsForDeliveryOrder($deliveryOrder->sales_order_id);
        $availableBatches = $deliveryOrderService->fetchAvailableBatches(
            $deliveryOrder->warehouse_id,
            $remainingSOItems->pluck('product_id')->unique()->values()->all()
        );

        $data = [
            'currentPage' => 'penjualan.pengiriman',
            'breadcrumb'  => [
                ['label' => 'Pengiriman', 'url' => route('sales.delivery_orders.index')],
                ['label' => 'Edit'],
            ],
            'deliveryOrder' => $deliveryOrder,
            'remainingSOItems' => $remainingSOItems,
            'availableBatches' => $availableBatches,
            'accounts' => $this->accountService->fetchAccountData(null),
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

    public function update(Request $request, DeliveryOrderService $deliveryOrderService, int $id)
    {
        if ($request->input('status') !== DeliveryOrderStatus::DRAFT->value) {
            $request->validate(
                [
                    'reference_number' => 'nullable|string|max:50',
                    'delivery_date' => 'required|date',
                    'status' => 'required|in:draft,finished',
                    'note' => 'nullable|string|max:1000',
                    'details' => 'required|array|min:1',
                    'details.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
                    'details.*.product_id' => 'required|exists:products,id',
                    'details.*.quantity' => 'required|numeric|min:0.0001',
                    'details.*.batches' => 'required|array|min:1',
                    'details.*.batches.*.product_batch_id' => 'required|exists:product_batches,id',
                    'details.*.batches.*.quantity' => 'required|numeric|min:0.0001',
                    'costs' => 'nullable|array',
                    'costs.*.account_id' => 'required_with:costs.*.amount|exists:chart_of_accounts,id',
                    'costs.*.description' => 'nullable|string|max:1000',
                    'costs.*.amount' => 'required_with:costs.*.account_id|numeric|min:0',
                ],
                [
                    'delivery_date.required' => 'Tanggal pengiriman harus diisi.',
                    'status.required' => 'Status pengiriman harus diisi.',
                    'details.required' => 'Daftar produk pengiriman harus diisi.',
                    'details.*.sales_order_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item SO.',
                    'details.*.product_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item SO.',
                    'details.*.quantity.required' => 'Terdapat produk yang belum diisi jumlah yang akan dikirim.',
                    'details.*.batches.required' => 'Terdapat produk yang belum memilih batch. Silakan pilih batch untuk setiap produk.',
                    'details.*.batches.*.product_batch_id.required' => 'Terdapat batch yang belum dipilih.',
                    'details.*.batches.*.quantity.required' => 'Terdapat batch yang belum diisi jumlahnya.',
                    'costs.*.account_id.required_with' => 'Akun harus dipilih jika jumlah biaya diisi.',
                    'costs.*.amount.required_with' => 'Jumlah biaya harus diisi jika akun dipilih.',
                ]
            );
        }

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
