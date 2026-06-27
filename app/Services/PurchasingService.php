<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasingService
{
    public function generatePONumber(): string
    {
        $prefix = 'PO';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = PurchaseOrder::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchPurchaseOrderTableData(Request $request) {
        $query = PurchaseOrder::with([
            'warehouse:id,name,code',
            'supplier:id,name,code'
        ])
        ->select(
            'id',
            'number',
            'order_date',
            'supplier_id',
            'warehouse_id',
            'due_date',
            'total_amount',
            'status'
        );

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $query = $query->orderBy('order_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function fetchPurchaseOrderByID(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::with([
            'items:id,purchase_order_id,product_id,quantity,unit_price,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'supplier:id,name,code',
            'warehouse:id,name,code',
            'salesPerson:id,name,code'
        ])
        ->select(
            'id',
            'supplier_id',
            'warehouse_id',
            'number',
            'reference_number',
            'order_date',
            'due_date',
            'payment_terms',
            'discount_amount',
            'transport_cost',
            'other_cost',
            'subtotal',
            'total_amount',
            'note',
            'status',
        )
        ->find($id);
    }

    public function storePurchaseOrder(Request $request): void
    {
        DB::transaction(function () use ($request) {
            $form =  PurchaseOrder::create(
                [
                    'company_id' => config('context.selected_company_id'),
                    'supplier_id' => $request->supplier_id,
                    'warehouse_id' => $request->warehouse_id,
                    'sales_person_id' => $request->sales_person_id,
                    'number' => $request->number,
                    'reference_number' => $request->reference_number,
                    'order_date' => $request->order_date,
                    'due_date' => $request->due_date,
                    'discount_amount' => $request->discount_amount,
                    'transport_cost' => $request->transport_cost,
                    'other_cost' => $request->other_cost,
                    'subtotal' => $request->subtotal,
                    'total_amount' => $request->subtotal - $request->discount_amount + $request->transport_cost + $request->other_cost,
                    'note' => $request->note,
                    'payment_terms' => $request->payment_terms,
                    'status' => $request->status,
                    'created_by' => auth()->user()->id,
                ]
            );

            foreach ($request->details as $detail) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $form->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'total_amount' => $detail['total_amount'],
                ]);
            }
        });
    }

    public function updatePurchaseOrder(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id) {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'sales_person_id' => $request->sales_person_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'order_date' => $request->order_date,
                'due_date' => $request->due_date,
                'discount_amount' => $request->discount_amount,
                'transport_cost' => $request->transport_cost,
                'other_cost' => $request->other_cost,
                'subtotal' => $request->subtotal,
                'total_amount' => $request->subtotal - $request->discount_amount + $request->transport_cost + $request->other_cost,
                'note' => $request->note,
                'payment_terms' => $request->payment_terms,
                'status' => $request->status,
            ]);

            // Delete existing items
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->delete();

            // Create new items
            foreach ($request->details as $detail) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'total_amount' => $detail['total_amount'],
                ]);
            }
        });
    }

    public function changePurchaseOrderStatus(int $id, string $status): void
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update(['status' => $status]);
    }
}
