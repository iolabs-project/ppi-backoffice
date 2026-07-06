<?php

namespace App\Services\Sales;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PaymentTerm;
use App\Enums\PurchaseInvoiceStatus;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryTransaction;
use App\Models\ProductBatch;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrderService
{
    public function generateSONumber(): string
    {
        $prefix = 'SO';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = SalesOrder::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchSalesOrderTableData(Request $request)
    {
        $query = SalesOrder::with([
            'items:id,sales_order_id,product_id,quantity,shipped_quantity,invoiced_quantity',
            'warehouse:id,name,code',
            'customer:id,name,code',
            'salesPerson:id,name,code',
        ])
            ->select(
                'id',
                'number',
                'order_date',
                'customer_id',
                'warehouse_id',
                'sales_person_id',
                'due_date',
                'total_amount',
                'status'
            )

            ->withSum('items as total_quantity', 'quantity')
            ->withSum('items as total_shipped_quantity', 'shipped_quantity')
            ->withSum('items as total_invoiced_quantity', 'invoiced_quantity');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('salesPerson', function ($q) use ($search) {
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

    public function fetchSalesOrderByID(int $id): ?SalesOrder
    {
        return SalesOrder::with([
            'items:id,sales_order_id,product_id,quantity,shipped_quantity,invoiced_quantity,unit_price,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'customer:id,name,code',
            'warehouse:id,name,code',
            'salesPerson:id,name,code',
            'creator:id,username'
        ])
            ->select(
                'id',
                'company_id',
                'customer_id',
                'warehouse_id',
                'sales_person_id',
                'number',
                'reference_number',
                'order_date',
                'due_date',
                'payment_terms',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'shipping_charge',
                'other_charge',
                'subtotal',
                'down_payment_amount',
                'down_payment_account_id',
                'total_amount',
                'note',
                'status',
                'created_by',
                'created_at',
                'updated_at',
            )
            ->find($id);
    }

    public function storeSalesOrder(Request $request): void
    {
        DB::transaction(function () use ($request) {
            $detailsCollection = collect($request->input('details', []));
            $subtotal = $detailsCollection->sum(function ($item) {
                return (($item['quantity'] ?? 0))
                    *
                    (($item['unit_price'] ?? 0))

                    *
                    (1 - (($item['discount_percentage'] ?? 0) / 100));
            });
            $discountAmount = $subtotal * ($request->discount_percentage ?? 0) / 100;
            $taxAmount = ($subtotal - $discountAmount) * ($request->tax_percentage ?? 0) / 100;

            $form =  SalesOrder::create(
                [
                    'company_id' => config('context.selected_company_id'),
                    'customer_id' => $request->customer_id,
                    'warehouse_id' => $request->warehouse_id,
                    'sales_person_id' => $request->sales_person_id,
                    'number' => $request->number,
                    'reference_number' => $request->reference_number,
                    'order_date' => $request->order_date,
                    'due_date' => $request->due_date,
                    'discount_percentage' => $request->discount_percentage,
                    'tax_percentage' => $request->tax_percentage,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'shipment_charge' => $request->shipment_charge,
                    'other_charge' => $request->other_charge,
                    'down_payment_amount' => $request->down_payment_amount,
                    'down_payment_remaining_amount' => $request->down_payment_amount,
                    'down_payment_account_id' => $request->down_payment_account_id,
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $request->shipment_charge + $request->other_charge,
                    'note' => $request->note,
                    'payment_terms' => $request->payment_terms,
                    'status' => $request->status,
                    'created_by' => auth()->user()->id,
                ]
            );

            foreach ($request->details as $detail) {
                SalesOrderItem::create([
                    'sales_order_id' => $form->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'],
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * ($detail['discount_percentage'] / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] / 100)),
                ]);
            }
        });
    }

    public function updateSalesOrder(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id) {
            $salesOrder = SalesOrder::findOrFail($id);
            $detailsCollection = collect($request->input('details', []));
            $subtotal = $detailsCollection->sum(function ($detail) {
                return (($detail['quantity'] ?? 0))
                    *
                    (($detail['unit_price'] ?? 0))
                    *
                    (1 - (($detail['discount_percentage'] ?? 0) / 100));
            });

            $discountAmount = $subtotal * ($request->discount_percentage ?? 0) / 100;
            $taxAmount = ($subtotal - $discountAmount) * ($request->tax_percentage ?? 0) / 100;

            $salesOrder->update([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'order_date' => $request->order_date,
                'due_date' => $request->due_date,
                'discount_percentage' => $request->discount_percentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $taxAmount,
                'shipping_charge' => $request->shipping_charge,
                'other_cost' => $request->other_cost,
                'subtotal' => $subtotal,
                'down_payment_amount' => $request->down_payment_amount,
                'down_payment_account_id' => $request->down_payment_account_id,
                'total_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $request->shipping_charge + $request->other_cost,
                'note' => $request->note,
                'payment_terms' => $request->payment_terms,
                'status' => $request->status,
            ]);

            // Delete existing items
            SalesOrderItem::where('sales_order_id', $salesOrder->id)->delete();

            // Create new items
            foreach ($request->details as $detail) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'] ?? 0,
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * (($detail['discount_percentage'] ?? 0) / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] ?? 0) / 100),
                ]);
            }
        });
    }

    public function changeSalesOrderStatus(int $id, string $status): void
    {
        $salesOrder = SalesOrder::findOrFail($id);
        $salesOrder->update(['status' => $status]);
    }

     public function fetchSOItemsForDeliveryOrder(int $id): Collection
    {
        $query = SalesOrderItem::with(['product:id,code,name,unit_id', 'product.unit:id,name,symbol'])
            ->where('sales_order_id', $id)
            ->orderBy('id', 'asc');

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_code' => $item->product->code,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'shipped_quantity' => $item->shipped_quantity,
                'remaining_quantity' => $item->quantity - $item->shipped_quantity,
                'unit_price' => $item->unit_price,
                'unit' => $item->product->unit->symbol,
                'discount_percentage' => $item->discount_percentage,
                'discount_amount' => $item->discount_amount,
            ];
        });
    }

}