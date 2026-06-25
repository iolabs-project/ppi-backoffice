<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\PaymentTerm;
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
            'purchaseOrders' => [],
        ];
        return view('purchasing.purchase-order.index', $data);
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
            'produk' => [],
            'kontak' => [],
            'gudang' => [],
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
        // dd($request->all());

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
}
