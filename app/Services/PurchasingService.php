<?php

namespace App\Services;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PaymentTerm;
use App\Enums\PurchaseInvoiceStatus;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryTransaction;
use App\Models\ProductBatch;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchasingService
{

    // Purchase Order
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

    public function fetchPurchaseOrderTableData(Request $request)
    {
        $query = PurchaseOrder::with([
            'items:id,purchase_order_id,product_id,quantity,received_quantity',
            'warehouse:id,name,code',
            'supplier:id,name,code',
            'goodsReceipts' => function ($query) {
                $query->select('id', 'purchase_order_id', 'status')
                    ->where('status', '<>', GoodsReceiptStatus::CANCELLED->value);
            },
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
            )

            ->withSum('items as total_quantity', 'quantity')
            ->withSum('items as total_received_quantity', 'received_quantity')
            ->withSum('items as total_invoiced_quantity', 'invoiced_quantity');

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
            'items:id,purchase_order_id,product_id,quantity,received_quantity,invoiced_quantity,unit_price,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'supplier:id,name,code',
            'warehouse:id,name,code',
            'salesPerson:id,name,code',
            'creator:id,username'
        ])
            ->select(
                'id',
                'company_id',
                'supplier_id',
                'warehouse_id',
                'number',
                'reference_number',
                'order_date',
                'due_date',
                'payment_terms',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'transport_cost',
                'other_cost',
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

    public function storePurchaseOrder(Request $request): void
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
                    'discount_percentage' => $request->discount_percentage,
                    'tax_percentage' => $request->tax_percentage,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'transport_cost' => $request->transport_cost,
                    'other_cost' => $request->other_cost,
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal - $discountAmount + $taxAmount + $request->transport_cost + $request->other_cost,
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
                    'discount_percentage' => $detail['discount_percentage'],
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * ($detail['discount_percentage'] / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] / 100)),
                ]);
            }
        });
    }

    public function updatePurchaseOrder(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id) {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
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

            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'sales_person_id' => $request->sales_person_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'order_date' => $request->order_date,
                'due_date' => $request->due_date,
                'discount_percentage' => $request->discount_percentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $taxAmount,
                'transport_cost' => $request->transport_cost,
                'other_cost' => $request->other_cost,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal - $discountAmount + $taxAmount + $request->transport_cost + $request->other_cost,
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
                    'discount_percentage' => $detail['discount_percentage'] ?? 0,
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * (($detail['discount_percentage'] ?? 0) / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] ?? 0) / 100),
                ]);
            }
        });
    }

    public function changePurchaseOrderStatus(int $id, string $status): void
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update(['status' => $status]);
    }

    public function fetchPOItemsForGoodsReceipt(int $id): Collection
    {
        $query = PurchaseOrderItem::with(['product:id,code,name,unit_id', 'product.unit:id,name,symbol'])
            ->where('purchase_order_id', $id)
            ->orderBy('id', 'asc');

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_code' => $item->product->code,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'received_quantity' => $item->received_quantity,
                'remaining_quantity' => $item->quantity - $item->received_quantity,
                'unit_price' => $item->unit_price,
                'unit' => $item->product->unit->symbol,
                'discount_percentage' => $item->discount_percentage,
                'discount_amount' => $item->discount_amount,
            ];
        });
    }

    // Goods Receipt
    public function generateGoodsReceiptNumber(): string
    {
        $prefix = 'GR';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = GoodsReceipt::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }
    public function fetchGoodsReceiptTableData(Request $request)
    {
        $query = GoodsReceipt::with([
            'purchaseOrder:id,number',
            'warehouse:id,name,code',
            'supplier:id,name,code',
            'items:id,goods_receipt_id,product_id,received_quantity,shrinkage_quantity',
        ])

            ->select(
                'id',
                'number',
                'receipt_date',
                'supplier_id',
                'warehouse_id',
                'purchase_order_id',
                'status'
            )->withSum('items as total_received_quantity', 'received_quantity')
            ->withSum('items as total_shrinkage_quantity', 'shrinkage_quantity');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchaseOrder', function ($q) use ($search) {
                        $q->where('number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $query = $query->orderBy('receipt_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function storeGoodsReceipt(Request $request): GoodsReceipt
    {
        $purchaseOrder =  $this->fetchPurchaseOrderByID($request->purchase_order_id);
        return GoodsReceipt::create([
            'company_id' => $purchaseOrder->company_id,
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'warehouse_id' => $purchaseOrder->warehouse_id,
            'number' => $this->generateGoodsReceiptNumber(),
            'receipt_date' => now(),
            'status' => GoodsReceiptStatus::DRAFT->value,
            'subtotal' => 0,
            'discount_percentage' => $purchaseOrder->discount_percentage,
            'discount_amount' => 0,
            'transport_cost' => $purchaseOrder->transport_cost,
            'other_cost' => $purchaseOrder->other_cost,
            'total_amount' => $purchaseOrder->total_amount,
            'created_by' => auth()->user()->id,
        ]);
    }

    public function fetchGoodsReceiptByID(int $id): ?GoodsReceipt
    {
        return GoodsReceipt::with([
            'items',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'items.purchaseOrderItem:id,quantity,received_quantity,discount_percentage,discount_amount,unit_price,total_amount',
            'purchaseOrder:id,number',
            'supplier:id,name,code',
            'warehouse:id,name,code',
            'creator:id,username'
        ])
            ->select(
                'id',
                'company_id',
                'purchase_order_id',
                'supplier_id',
                'warehouse_id',
                'number',
                'reference_number',
                'receipt_date',
                'status',
                'subtotal',
                'discount_percentage',
                'discount_amount',
                'transport_cost',
                'other_cost',
                'total_amount',
                'note',
                'created_by',
                'created_at',
                'updated_at'
            )
            ->find($id);
    }

    public function updateGoodsReceipt(Request $request, int $id): void
    {
        $header = GoodsReceipt::findOrFail($id);
        $requestCollection = collect($request->except('details'));
        $detailsCollection = collect($request->input('details', []));

        DB::transaction(function () use ($requestCollection, $detailsCollection, $header) {
            $subtotal = $detailsCollection->sum(function ($item) {
                return (($item['received_quantity'] ?? 0))
                    *
                    (($item['unit_price'] ?? 0))

                    *
                    (1 - (($item['discount_percentage'] ?? 0) / 100));
            });
            $discountAmount = $requestCollection->get('discount_amount', 0);
            $transportCost = $requestCollection->get('transport_cost', 0);
            $otherCost = $requestCollection->get('other_cost', 0);
            $totalAmount = $subtotal - $discountAmount + $transportCost + $otherCost;
            $header->update([
                'reference_number' => $requestCollection->get('reference_number', null),
                'receipt_date' => $requestCollection->get('receipt_date'),
                'status' => $requestCollection->get('status'),
                'subtotal' => $subtotal,
                'transport_cost' => $transportCost,
                'other_cost' => $otherCost,
                'total_amount' => $totalAmount,
                'note' => $requestCollection->get('note', null),
            ]);

            GoodsReceiptItem::where('goods_receipt_id', $header->id)->delete();
            if ($requestCollection->get('status') === GoodsReceiptStatus::DRAFT->value) {
                $this->saveDraftGoodsReceipt($header, $detailsCollection);
            } elseif ($requestCollection->get('status') === GoodsReceiptStatus::FINISHED->value) {
                $this->finalizeGoodsReceipt($header, $detailsCollection, $transportCost, $otherCost, $discountAmount);
            }
        });
    }

    private function saveDraftGoodsReceipt(GoodsReceipt $goodsReceipt, Collection $detailsCollection): void
    {

        foreach ($detailsCollection as $item) {
            $receivedQty = (float) ($item['received_quantity'] ?? 0);
            $expectedQty = (float) ($item['expected_quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discountPercentage = (float) ($item['discount_percentage'] ?? 0);
            $discountAmount = $receivedQty * $unitPrice * ($discountPercentage / 100);
            GoodsReceiptItem::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'product_id' => $item['product_id'],
                'batch_number' => $item['batch_number'],
                'expected_quantity' => $expectedQty,
                'shrinkage_quantity' => $expectedQty - $receivedQty,
                'received_quantity' => $receivedQty,
                'unit_price' => $unitPrice,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'allocated_cost' => 0,
                'unit_cost' => 0,
                'total_cost' => 0,
            ]);
        }
    }

    private function finalizeGoodsReceipt(GoodsReceipt $goodsReceipt, Collection $detailsCollection, float $transportCost, float $otherCost, float $discountAmount): void
    {
        $additionalCost = $transportCost + $otherCost - $discountAmount;
        $totalWeight = $detailsCollection->sum(function ($item) {
            return $item['received_quantity'];
        });
        $costPerUnit = $totalWeight > 0 ? $additionalCost / $totalWeight : 0;



        foreach ($detailsCollection as $item) {
            $receivedQty = (float) ($item['received_quantity'] ?? 0);
            $expectedQty = (float) ($item['expected_quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discountPercentage = (float) ($item['discount_percentage'] ?? 0);
            $discountAmount = $receivedQty * $unitPrice * ($discountPercentage / 100);


            $allocatedCost = $costPerUnit * $receivedQty;
            $discountPerUnit = $discountAmount / $receivedQty;
            $unitCost = $unitPrice + $costPerUnit - $discountPerUnit;
            $totalCost = $unitCost * $receivedQty;

            if ($this->validateBatchNumber($item['batch_number'], $item['product_id'], $goodsReceipt->company_id)) {
                throw ValidationException::withMessages([
                    'batch_number' => "Nomor batch '{$item['batch_number']}' untuk produk ini sudah ada.",
                ]);
            }

            $goodsReceiptItem = GoodsReceiptItem::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'product_id' => $item['product_id'],
                'batch_number' => $item['batch_number'],
                'expected_quantity' => $expectedQty,
                'shrinkage_quantity' => $expectedQty - $receivedQty,
                'received_quantity' => $receivedQty,
                'unit_price' => $unitPrice,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'allocated_cost' => $allocatedCost,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
            ]);

            $batch = ProductBatch::create([
                'company_id' => $goodsReceipt->company_id,
                'warehouse_id' => $goodsReceipt->warehouse_id,
                'product_id' => $goodsReceiptItem->product_id,
                'goods_receipt_item_id' => $goodsReceiptItem->id,
                'batch_number' => $goodsReceiptItem->batch_number,
                'quantity' => $goodsReceiptItem->received_quantity,
                'unit_cost' => $goodsReceiptItem->unit_cost,
            ]);

            $latestTransaction = InventoryTransaction::where('company_id', $goodsReceipt->company_id)
                ->where('product_id', $goodsReceiptItem->product_id)
                ->where('warehouse_id', $goodsReceipt->warehouse_id)
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->first();

            InventoryTransaction::create([
                'company_id' => $goodsReceipt->company_id,
                'warehouse_id' => $goodsReceipt->warehouse_id,
                'product_id' => $goodsReceiptItem->product_id,
                'product_batch_id' => $batch->id,
                'type' => 'purchase',
                'direction' => 1,
                'quantity' => $goodsReceiptItem->received_quantity,
                'unit_cost' => $goodsReceiptItem->unit_cost,
                'total_cost' => $goodsReceiptItem->total_cost,
                'stock_before' => $latestTransaction ? $latestTransaction->stock_after : 0,
                'stock_after' => ($latestTransaction ? $latestTransaction->stock_after : 0) + $goodsReceiptItem->received_quantity,
                'reference_type' => GoodsReceipt::class,
                'reference_id' => $goodsReceipt->id,
                'transaction_date' => now(),
                'note' => 'Penerimaan Barang dari GR #' . $goodsReceipt->number,
            ]);

            PurchaseOrderItem::where('id', $goodsReceiptItem->purchase_order_item_id)
                ->increment('received_quantity', $goodsReceiptItem->received_quantity);

            // PurchaseOrder::where('id', $goodsReceipt->purchase_order_id)
            //     ->whereColumn('received_quantity', '>=', 'quantity')
            //     ->update(['status' => 'closed']);

            PurchaseOrder::where('id', $goodsReceipt->purchase_order_id)
                ->whereDoesntHave('items', function ($query) {
                    $query->whereColumn('received_quantity', '<', 'quantity');
                })
                ->update(['status' => 'closed']);
        }
    }

    private function validateBatchNumber(string $batchNumber, int $productID, int $companyID): bool
    {
        return GoodsReceiptItem::where('batch_number', $batchNumber)
            ->where('product_id', $productID)
            ->whereHas('goodsReceipt', function ($query) use ($companyID) {
                $query->where('company_id', $companyID);
            })
            ->exists();
    }

    public function changeGoodsReceiptStatus(int $id, string $status): void
    {
        $goodsReceipt = GoodsReceipt::findOrFail($id);
        $goodsReceipt->update(['status' => $status]);
    }

    // Purchase Invoice
    public function generatePurchaseInvoiceNumber(): string
    {
        $prefix = 'PI';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = PurchaseInvoice::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);



        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchPurchaseInvoiceTableData(Request $request)
    {
        $query = PurchaseInvoice::with([
            'purchaseOrder:id,number',
            'supplier:id,name,code',
            'warehouse:id,name,code',
        ])
            ->select(
                'id',
                'number',
                'invoice_date',
                'supplier_id',
                'warehouse_id',
                'purchase_order_id',
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
                    })
                    ->orWhereHas('purchaseOrder', function ($q) use ($search) {
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

    public function storePurchaseInvoice(Request $request): PurchaseInvoice
    {
        $purchaseOrder =  $this->fetchPurchaseOrderByID($request->purchase_order_id);
        return PurchaseInvoice::create([
            'company_id' => $purchaseOrder->company_id,
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'warehouse_id' => $purchaseOrder->warehouse_id,
            'number' => $this->generatePurchaseInvoiceNumber(),
            'invoice_date' => now(),
            'payment_terms' => $request->payment_terms,
            'due_date' => now()->addDays(PaymentTerm::day($request->payment_terms)),
            'status' => PurchaseInvoiceStatus::DRAFT->value,
            'subtotal' => 0,
            'discount_percentage' => $purchaseOrder->discount_percentage,
            'discount_amount' => 0,
            'tax_percentage' => $purchaseOrder->tax_percentage,
            'tax_amount' => 0,
            'total_amount' => 0,
            'created_by' => auth()->user()->id,
        ]);
    }

    public function fetchPurchaseInvoiceByID(int $id): ?PurchaseInvoice
    {
        return PurchaseInvoice::with([
            'purchaseOrder:id,number',
            'items:id,purchase_invoice_id,goods_receipt_item_id,product_id,quantity,unit_price,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'supplier:id,name,code',
            'warehouse:id,name,code',
            'creator:id,username'
        ])
            ->select(
                'id',
                'company_id',
                'supplier_id',
                'warehouse_id',
                'number',
                'reference_number',
                'order_date',
                'due_date',
                'payment_terms',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
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
}
