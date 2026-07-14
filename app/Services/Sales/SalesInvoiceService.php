<?php

namespace App\Services\Sales;

use App\Enums\DeliveryOrderStatus;
use App\Enums\PaymentTerm;
use App\Enums\SalesInvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Company;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\DeliveryOrderItemBatch;
use App\Models\InventoryTransaction;
use App\Models\ProductBatch;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceCharge;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderCharge;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesInvoiceService
{

    private SalesOrderService $salesOrderService;

    public function __construct(SalesOrderService $salesOrderService)
    {
        $this->salesOrderService = $salesOrderService;
    }

    public function generateSINumber(): string
    {
        $prefix = 'SI';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = SalesInvoice::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchSalesInvoiceTableData(Request $request)
    {
        $query = SalesInvoice::with([
            'salesOrder:id,number',
            'customer:id,name,code',
            'warehouse:id,name,code',
        ])
            ->select(
                'id',
                'number',
                'invoice_date',
                'due_date',
                'customer_id',
                'warehouse_id',
                'sales_order_id',
                'total_amount',
                'status'
            );

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
                    ->orWhereHas('salesOrder', function ($q) use ($search) {
                        $q->where('number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $query = $query->orderBy('invoice_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function storeSalesInvoice(Request $request): SalesInvoice
    {
        $salesOrder =  $this->salesOrderService->fetchSalesOrderByID($request->sales_order_id);
        return DB::transaction(function () use ($salesOrder) {
            $invoice =  SalesInvoice::create([
                'company_id' => $salesOrder->company_id,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'sales_person_id' => $salesOrder->sales_person_id,
                'warehouse_id' => $salesOrder->warehouse_id,
                'number' => $this->generateSINumber(),
                'invoice_date' => now(),
                'payment_terms' => $salesOrder->payment_terms,
                'due_date' => now()->addDays(PaymentTerm::day($salesOrder->payment_terms)),
                'status' => SalesInvoiceStatus::DRAFT->value,
                'subtotal' => 0,
                'discount_percentage' => $salesOrder->discount_percentage,
                'discount_amount' => 0,
                'tax_percentage' => $salesOrder->tax_percentage,
                'tax_amount' => 0,
                'total_amount' => 0,
                'created_by' => auth()->user()->id,
            ]);

            foreach ($salesOrder->charges as $charge) {
                SalesInvoiceCharge::create([
                    'sales_invoice_id' => $invoice->id,
                    'account_id' => $charge->account_id,
                    'description' => $charge->description,
                    'is_taxable' => $charge->is_taxable,
                    'amount' => $charge->amount,
                ]);
            }

            return $invoice;
        });
    }

    public function updateSalesInvoice(Request $request, int $id)
    {
        $salesInvoice = SalesInvoice::findOrFail($id);
        $detailsCollection = collect($request->input('details', []));
        $chargesCollection = collect($request->input('charges', []));
        $subtotal = $detailsCollection->sum(function ($detail) {
            return (($detail['quantity'] ?? 0))
                *
                (($detail['unit_price'] ?? 0))
                *
                (1 - (($detail['discount_percentage'] ?? 0) / 100));
        });
        $chargesTotal = $chargesCollection->sum('amount');
        $taxableChargeAmount = $chargesCollection->where('is_taxable', true)->sum('amount');
        $discountAmount = $subtotal * ($request->discount_percentage ?? 0) / 100;
        $taxAmount = ($subtotal - $discountAmount + $taxableChargeAmount) * ($request->tax_percentage ?? 0) / 100;

        DB::transaction(function () use ($salesInvoice, $request, $subtotal, $discountAmount, $taxAmount, $chargesTotal) {
            $salesInvoice->update([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'sales_person_id' => $request->sales_person_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'discount_percentage' => $request->discount_percentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'down_payment_amount' => $request->down_payment_amount,
                'total_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $chargesTotal,
                'remaining_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $chargesTotal,
                'note' => $request->note,
                'payment_terms' => $request->payment_terms,
                'status' => $request->status,
            ]);

            SalesInvoiceItem::where('sales_invoice_id', $salesInvoice->id)->delete();
            SalesInvoiceCharge::where('sales_invoice_id', $salesInvoice->id)->delete();
        });

        foreach ($request->details as $detail) {
            SalesInvoiceItem::create([
                'sales_invoice_id' => $salesInvoice->id,
                'delivery_order_item_id' => $detail['delivery_order_item_id'],
                'sales_order_item_id' => $detail['sales_order_item_id'],
                'product_id' => $detail['product_id'],
                'quantity' => $detail['quantity'],
                'unit_price' => $detail['unit_price'],
                'discount_percentage' => $detail['discount_percentage'] ?? 0,
                'discount_amount' => $detail['quantity'] * $detail['unit_price'] * (($detail['discount_percentage'] ?? 0) / 100),
                'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] ?? 0) / 100),
            ]);

            if ($request->status === SalesInvoiceStatus::OPEN->value) {
                SalesOrderItem::where('id', $detail['sales_order_item_id'])
                    ->increment('invoiced_quantity', $detail['quantity']);
            }
        }

        foreach ($request->input('charges', []) as $charge) {
            SalesInvoiceCharge::create([
                'sales_invoice_id' => $salesInvoice->id,
                'account_id' => $charge['account_id'],
                'description' => $charge['description'] ?? null,
                'is_taxable' => $charge['is_taxable'] ?? false,
                'amount' => $charge['amount'],
            ]);
        }

        if ($request->status === SalesInvoiceStatus::OPEN->value) {
            SalesOrder::where('id', $salesInvoice->sales_order_id)
                ->decrement('down_payment_remaining_amount', $request->down_payment_amount);
        }
    }

    public function fetchSalesInvoiceByID(int $id): ?SalesInvoice
    {
        return SalesInvoice::with([
            'salesOrder:id,number,down_payment_amount,down_payment_remaining_amount',
            'items:id,sales_invoice_id,sales_order_item_id,delivery_order_item_id,product_id,quantity,unit_price,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'charges:id,sales_invoice_id,account_id,description,amount,is_taxable',
            'charges.account:id,code,name,category_id',
            'customer:id,name,code',
            'salesPerson:id,name,code',
            'warehouse:id,name,code',
            'creator:id,username'
        ])
            ->select(
                'id',
                'sales_order_id',
                'company_id',
                'customer_id',
                'sales_person_id',
                'warehouse_id',
                'number',
                'reference_number',
                'invoice_date',
                'due_date',
                'payment_terms',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'down_payment_amount',
                'subtotal',
                'total_amount',
                'note',
                'status',
                'created_by',
                'created_at',
                'updated_at',
            )
            ->find($id);
    }

    public function cancelSalesInvoice(int $id): void
    {
        $salesInvoice = SalesInvoice::findOrFail($id);

        if ($salesInvoice->status === SalesInvoiceStatus::CANCELLED->value) {
            throw ValidationException::withMessages([
                'status' => "Tagihan ini sudah dibatalkan.",
            ]);
        }

        $isDraft = $salesInvoice->status === SalesInvoiceStatus::DRAFT->value;
        $isOpenWithNoPayment = $salesInvoice->status === SalesInvoiceStatus::OPEN->value
            && (float) $salesInvoice->total_amount === (float) $salesInvoice->remaining_amount;

        if (!$isDraft && !$isOpenWithNoPayment) {
            throw ValidationException::withMessages([
                'status' => "Tagihan ini tidak dapat dibatalkan.",
            ]);
        }
        DB::transaction(function () use ($salesInvoice) {
            foreach ($salesInvoice->items as $item) {
                SalesOrderItem::where('id', $item->sales_order_item_id)
                    ->decrement('invoiced_quantity', $item->quantity);
            }
            $salesInvoice->update(['status' => SalesInvoiceStatus::CANCELLED->value]);
            SalesOrder::where('id', $salesInvoice->sales_order_id)
                ->increment('down_payment_remaining_amount', $salesInvoice->down_payment_amount);
        });
    }
}
