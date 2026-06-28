<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\PaymentTerm;
use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Services\PurchasingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $data = [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [['label' => 'Pembelian', 'url' => route('pembelian.index')]],
            'status' => PurchaseOrderStatus::dropdownOptions(),
        ];
        return view('purchasing.purchase-order.index', $data);
    }

    public function datatable(Request $request, PurchasingService $purchasingService) {
        $data = $purchasingService->fetchPurchaseOrderTableData($request);
        return response()->json($data);
    }

    public function create(PurchasingService $purchasingService)
    {
        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Tambah PO'],
            ],
            'number' => $purchasingService->generatePONumber(),
            'paymentTerms' => PaymentTerm::dropdownOptions()
        ];
        return view('purchasing.purchase-order.create', $data);
    }

    public function store(Request $request, PurchasingService $purchasingService)
    {
        // dd($request->all());
        $request->validate([
            'supplier_id' => 'required|exists:contacts,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'number' => 'required|string|max:50|unique:purchase_orders,number',
            'reference_number' => 'nullable|string|max:50',
            'order_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
            'discount_amount' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,open',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:1',
            'details.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $purchasingService->storePurchaseOrder($request);
            return response()->json(['redirect' => route('purchasings.purchasing_orders.index'), 'message' => 'Purchase Order berhasil dibuat.']);
       } catch (\Exception $e) {
            Log::error('Error PurchaseOrderController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat Purchase Order. Silakan coba lagi.'], 500);
        }
    }

    public function show(PurchasingService $purchasingService, int $id)
    {
        $purchaseOrder = $purchasingService->fetchPurchaseOrderByID($id);
        if (!$purchaseOrder) {
            abort(404, 'Purchase Order tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Detail PO'],
            ],
            'purchaseOrder' => $purchaseOrder,
        ];
        return view('purchasing.purchase-order.show', $data);
    }

    public function edit(PurchasingService $purchasingService, int $id)
    {
        $purchaseOrder = $purchasingService->fetchPurchaseOrderByID($id);
        if (!$purchaseOrder) {
            abort(404, 'Purchase Order tidak ditemukan.');
        }

        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Edit PO'],
            ],
            'purchaseOrder' => $purchaseOrder,
            'paymentTerms' => PaymentTerm::dropdownOptions()
        ];
        return view('purchasing.purchase-order.edit', $data);
    }

    public function update(Request $request, PurchasingService $purchasingService, int $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:contacts,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'number' => 'required|string|max:50|unique:purchase_orders,number,' . $id,
            'reference_number' => 'nullable|string|max:50',
            'order_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
            'discount_amount' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,open',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:1',
            'details.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $purchasingService->updatePurchaseOrder($request, $id);
            return response()->json(['redirect' => route('purchasings.purchasing_orders.index'), 'message' => 'Purchase Order berhasil diperbarui.']);
        } catch (\Exception $e) {
            Log::error('Error PurchaseOrderController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Purchase Order. Silakan coba lagi.'], 500);
        }
    }

    public function open(PurchasingService $purchasingService, int $id)
    {
        try {
            $purchasingService->changePurchaseOrderStatus($id, PurchaseOrderStatus::OPEN->value);
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

    public function close(PurchasingService $purchasingService, int $id)
    {
        try {
            $purchasingService->changePurchaseOrderStatus($id, PurchaseOrderStatus::CLOSED->value);
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

    public function cancel(PurchasingService $purchasingService, int $id)
    {
        try {
            $purchasingService->changePurchaseOrderStatus($id, PurchaseOrderStatus::CANCELLED->value);
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

    // public function itemOptions(PurchasingService $purchasingService, Request $request)
    // {
    //     $options = $purchasingService->fetchPurchaseOrderItemOptions($request);
    //     return response()->json($options);
    // }
}
