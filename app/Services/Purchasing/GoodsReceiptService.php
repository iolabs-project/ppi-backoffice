<?php

namespace App\Services\Purchasing;

use App\Enums\GoodsReceiptStatus;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptCost;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryTransaction;
use App\Models\ProductBatch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{

    private PurchaseOrderService $purchaseOrderService;
    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }
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
        $purchaseOrder =  $this->purchaseOrderService->fetchPurchaseOrderByID($request->purchase_order_id);
        $goodsReceipt = GoodsReceipt::create([
            'company_id' => $purchaseOrder->company_id,
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'warehouse_id' => $purchaseOrder->warehouse_id,
            'number' => $this->generateGoodsReceiptNumber(),
            'receipt_date' => now(),
            'status' => GoodsReceiptStatus::DRAFT->value,
            'subtotal' => 0,
            'discount_percentage' => $purchaseOrder->discount_percentage ?? 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'created_by' => auth()->user()->id,
        ]);

        foreach ($purchaseOrder->costs as $cost) {
            GoodsReceiptCost::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'account_id' => $cost->account_id,
                'description' => $cost->description,
                'billed_by' => $cost->billed_by,
                'is_inventory_cost' => $cost->is_inventory_cost,
                'amount' => $cost->amount,
            ]);
        }

        return $goodsReceipt;
    }

    public function fetchGoodsReceiptByID(int $id): ?GoodsReceipt
    {
        return GoodsReceipt::with([
            'items',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'items.purchaseOrderItem:id,quantity,received_quantity,discount_percentage,discount_amount,unit_price,total_amount',
            'costs:id,goods_receipt_id,account_id,description,amount,billed_by,is_inventory_cost',
            'costs.account:id,code,name,category_id',
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
        $requestCollection = collect($request->except(['details', 'costs']));
        $detailsCollection = collect($request->input('details', []));
        $costsCollection = collect($request->input('costs', []));

        DB::transaction(function () use ($request, $requestCollection, $detailsCollection, $costsCollection, $header) {
            $subtotal = $detailsCollection->sum(function ($item) {
                return (($item['received_quantity'] ?? 0))
                    *
                    (($item['unit_price'] ?? 0))

                    *
                    (1 - (($item['discount_percentage'] ?? 0) / 100));
            });
            $costAmount = $costsCollection->sum(function ($cost) {
                return $cost['amount'] ?? 0;
            });
            $discountAmount = $subtotal * ($requestCollection->get('discount_percentage', 0)) / 100;
            $header->update([
                'reference_number' => $requestCollection->get('reference_number', null),
                'receipt_date' => $requestCollection->get('receipt_date'),
                'status' => $requestCollection->get('status'),
                'subtotal' => $subtotal,
                'discount_percentage' => $requestCollection->get('discount_percentage', 0),
                'discount_amount' => $discountAmount,
                'note' => $requestCollection->get('note', null),
            ]);

            GoodsReceiptCost::where('goods_receipt_id', $header->id)->delete();
            GoodsReceiptItem::where('goods_receipt_id', $header->id)->delete();
            if ($requestCollection->get('status') === GoodsReceiptStatus::DRAFT->value) {
                $this->saveDraftGoodsReceipt($header, $detailsCollection, $costsCollection);
            } elseif ($requestCollection->get('status') === GoodsReceiptStatus::FINISHED->value) {
                $this->finalizeGoodsReceipt($header, $detailsCollection, $costsCollection);
            }
        });
    }

    private function saveDraftGoodsReceipt(GoodsReceipt $goodsReceipt, Collection $detailsCollection, Collection $costsCollection): void
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
                'subtotal' => $receivedQty * $unitPrice,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'unit_cost' => 0,
                'total_amount' => $receivedQty * $unitPrice - $discountAmount,
            ]);
        }

        foreach ($costsCollection as $cost) {
            GoodsReceiptCost::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'account_id' => $cost['account_id'],
                'description' => $cost['description'] ?? null,
                'billed_by' => $cost['billed_by'] ?? 'supplier',
                'is_inventory_cost' => $cost['is_inventory_cost'] ?? false,
                'amount' => $cost['amount'],
            ]);
        }
    }

    private function finalizeGoodsReceipt(GoodsReceipt $goodsReceipt, Collection $detailsCollection, Collection $costsCollection): void
    {
        $additionalCost = $costsCollection->sum(function ($cost) {
            return ($cost['is_inventory_cost'] ?? false) ? ($cost['amount'] ?? 0) : 0;
        });
        $discountAmount = (float) ($goodsReceipt->discount_amount ?? 0);
        $totalQty = $detailsCollection->sum(function ($item) {
            return $item['received_quantity'];
        });
        $totalSubtotal = $detailsCollection->sum(function ($item) {
            $subtotal = $item['received_quantity'] * $item['unit_price'];
            $discountAmount = $subtotal * ($item['discount_percentage'] / 100);
            return $subtotal - $discountAmount;
        });

        foreach ($costsCollection as $cost) {
            GoodsReceiptCost::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'account_id' => $cost['account_id'],
                'description' => $cost['description'] ?? null,
                'billed_by' => $cost['billed_by'] ?? 'supplier',
                'is_inventory_cost' => $cost['is_inventory_cost'] ?? false,
                'amount' => $cost['amount'],
            ]);
        }

        foreach ($detailsCollection as $item) {
            $expectedQty = (float) ($item['expected_quantity'] ?? 0);
            $qty = (float) ($item['received_quantity'] ?? 0);
            $unitDiscountPercentage = (float) ($item['discount_percentage'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $subtotal = $qty * $unitPrice;
            $unitDiscountAmount = $subtotal * ($unitDiscountPercentage / 100);
            $total = $subtotal - $unitDiscountAmount;

            if ($qty <= 0) {
                $unitCost = 0;
            } else {

                $qtyRatio = $totalQty > 0 ? ($qty / $totalQty) : 0;
                $valueRatio = $totalSubtotal > 0 ? ($total / $totalSubtotal) : 0;

                $addtionalCostPerUnit = $qty > 0 ? ($additionalCost * $qtyRatio) / $qty : 0;
                $discountAmountPerUnit = $qty > 0 ? ($discountAmount * $valueRatio) / $qty : 0;

                $unitCost = $unitPrice - ($qty > 0 ? $unitDiscountAmount / $qty : 0) + $addtionalCostPerUnit - $discountAmountPerUnit;
            }

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
                'shrinkage_quantity' => $expectedQty - $qty,
                'received_quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_percentage' => $unitDiscountPercentage,
                'discount_amount' => $unitDiscountAmount,
                'unit_cost' => $unitCost,
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
                'total_cost' => $goodsReceiptItem->unit_cost * $goodsReceiptItem->received_quantity,
                'stock_before' => $latestTransaction ? $latestTransaction->stock_after : 0,
                'stock_after' => ($latestTransaction ? $latestTransaction->stock_after : 0) + $goodsReceiptItem->received_quantity,
                'reference_type' => GoodsReceipt::class,
                'reference_id' => $goodsReceipt->id,
                'transaction_date' => now(),
                'note' => 'Penerimaan Barang dari GR #' . $goodsReceipt->number,
            ]);

            PurchaseOrderItem::where('id', $goodsReceiptItem->purchase_order_item_id)
                ->increment('received_quantity', $goodsReceiptItem->received_quantity);

            PurchaseOrder::where('id', $goodsReceipt->purchase_order_id)
                ->whereDoesntHave('items', function ($query) {
                    $query->whereColumn('received_quantity', '<', 'quantity');
                })
                ->update(['status' => 'closed']);

            // TODO: Create drafted cost invoice for costs that the billed_by is not 'supplier' 
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

    public function fetchGRItemsForPurchaseInvoice(int $id): Collection
    {
        $query = GoodsReceiptItem::with(['product:id,code,name,unit_id', 'product.unit:id,name,symbol'])
            ->whereHas('goodsReceipt', function ($query) use ($id) {
                $query->where('purchase_order_id', $id)
                ->where('status', GoodsReceiptStatus::FINISHED->value);
            })
            ->orderBy('id', 'asc');

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'purchase_order_item_id' => $item->purchase_order_item_id,
                'product_id' => $item->product_id,
                'product_code' => $item->product->code,
                'product_name' => $item->product->name,
                'batch_number' => $item->batch_number,
                'quantity' => $item->received_quantity,
                'unit_price' => $item->unit_price,
                'unit' => $item->product->unit->symbol,
                'discount_percentage' => $item->discount_percentage,
                'discount_amount' => $item->discount_amount,
            ];
        });
    }
}
