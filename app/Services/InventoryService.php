<?php

namespace App\Services;

use App\Enums\DeliveryOrderStatus;
use App\Enums\GoodsReceiptStatus;
use App\Enums\InventoryTransactionTypeEnum;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\GoodsReceipt;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\InventoryTransaction;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\SalesInvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }
    public function fetchInventoryStock(int $companyID, int | null $warehouseID = null, array $productIDs = []): Collection
    {
        $data = ProductStock::with([
            'product:id,code,name,unit_id',
            'product.unit:id,name,symbol',
            'warehouse:id,name',
        ])
            ->select(
                'id',
                'company_id',
                'product_id',
                'warehouse_id',
                'quantity',
                'reserved_quantity',
                'average_unit_cost'
            )
            ->where('company_id', $companyID)
            ->when($warehouseID, function ($query) use ($warehouseID) {
                $query->where('warehouse_id', $warehouseID);
            })
            ->whereRaw('quantity - reserved_quantity > 0')
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when(!empty($productIDs), function ($query) use ($productIDs) {
                $query->whereIn('product_id', $productIDs);
            })
            ->get();

        return $data;
    }

    public function fetchInventoryStockTableData(int $companyID, int $perPage, int | null $warehouseID = null, array $productIDs = [], string | null $search = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $data = ProductStock::with([
            'product:id,code,name,unit_id',
            'product.unit:id,name,symbol',
            'warehouse:id,name',
        ])
            ->select(
                'id',
                'company_id',
                'product_id',
                'warehouse_id',
                'quantity',
                'reserved_quantity',
                'average_unit_cost'
            )
            ->where('company_id', $companyID)
            ->when($warehouseID, function ($query) use ($warehouseID) {
                $query->where('warehouse_id', $warehouseID);
            })
            ->whereRaw('quantity - reserved_quantity > 0')
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when(!empty($productIDs), function ($query) use ($productIDs) {
                $query->whereIn('product_id', $productIDs);
            })
            ->when($search !== null, function ($query) use ($search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->paginate($perPage);

        return $data;
    }

    public function fetchInventoryBatches(int $companyID, int | null $warehouseID = null, array $productIDs = [], bool | null $onlyAvailable = null): Collection
    {
        return ProductBatch::with([
            'product:id,code,name,unit_id',
            'product.unit:id,name,symbol',
            'warehouse:id,name',
        ])
            ->select(
                'id',
                'company_id',
                'warehouse_id',
                'product_id',
                'batch_number',
                'initial_quantity',
                'quantity',
                'reserved_quantity',
                'unit_cost'
            )
            ->where('company_id', $companyID)
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($warehouseID !== null, function ($query) use ($warehouseID) {
                $query->where('warehouse_id', $warehouseID);
            })
            ->when($onlyAvailable !== null, function ($query) use ($onlyAvailable) {
                if ($onlyAvailable) {
                    $query->whereRaw('quantity - reserved_quantity > 0');
                } else {
                    $query->whereRaw('quantity - reserved_quantity <= 0');
                }
            })
            ->when(!empty($productIDs), function ($query) use ($productIDs) {
                $query->whereIn('product_id', $productIDs);
            })
            ->orderBy('id', 'asc')
            ->get();
    }

    public function fetchInventoryBatchTableData(int $companyID, int $perPage, int | null $warehouseID = null, array $productIDs = [], bool | null $onlyAvailable = null, string | null $search = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ProductBatch::with([
            'product:id,code,name,unit_id',
            'product.unit:id,name,symbol',
            'warehouse:id,name',
        ])
            ->select(
                'id',
                'company_id',
                'warehouse_id',
                'product_id',
                'batch_number',
                'initial_quantity',
                'quantity',
                'reserved_quantity',
                'unit_cost'
            )
            ->where('company_id', $companyID)
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($warehouseID !== null, function ($query) use ($warehouseID) {
                $query->where('warehouse_id', $warehouseID);
            })
            ->when($onlyAvailable !== null, function ($query) use ($onlyAvailable) {
                if ($onlyAvailable) {
                    $query->whereRaw('quantity - reserved_quantity > 0');
                } else {
                    $query->whereRaw('quantity - reserved_quantity <= 0');
                }
            })
            ->when(!empty($productIDs), function ($query) use ($productIDs) {
                $query->whereIn('product_id', $productIDs);
            })
            ->when($search !== null, function ($query) use ($search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->paginate($perPage);
    }

    public function receiveInventoryFromGR(GoodsReceipt $goodsReceipt, GoodsReceiptItem $goodsReceiptItem): ProductBatch
    {
        $productBatch = ProductBatch::create([
            'company_id' => $goodsReceipt->company_id,
            'warehouse_id' => $goodsReceipt->warehouse_id,
            'product_id' => $goodsReceiptItem->product_id,
            'goods_receipt_item_id' => $goodsReceiptItem->id,
            'batch_number' => $goodsReceiptItem->batch_number,
            'initial_quantity' => $goodsReceiptItem->received_quantity,
            'quantity' => $goodsReceiptItem->received_quantity,
            'unit_cost' => $goodsReceiptItem->unit_cost,
        ]);

        $this->insertInventoryTransaction(goodsReceipt: $goodsReceipt, goodsReceiptItem: $goodsReceiptItem, batch: $productBatch);
        $this->recalculateMovingAverageCost(companyID: $goodsReceipt->company_id, productID: $goodsReceiptItem->product_id, warehouseID: $goodsReceipt->warehouse_id);

        return $productBatch;
    }
    public function issueInventoryFromDO(DeliveryOrder $deliveryOrder, int $productID, int $productBatchID, float $quantity, ?string $batchNumberHint = null): ProductBatch
    {
        $productBatch = ProductBatch::where('id', $productBatchID)
            ->where('warehouse_id', $deliveryOrder->warehouse_id)
            ->where('product_id', $productID)
            ->lockForUpdate()
            ->first();

        $productStock = ProductStock::where('company_id', $deliveryOrder->company_id)
            ->where('warehouse_id', $deliveryOrder->warehouse_id)
            ->where('product_id', $productID)
            ->lockForUpdate()
            ->first();

        if (!$productBatch) {
            throw ValidationException::withMessages([
                'details' => "Batch '{$batchNumberHint}' tidak ditemukan atau bukan milik produk/gudang ini.",
            ]);
        }

        if ($productBatch->available_quantity < $quantity) {
            throw ValidationException::withMessages([
                'details' => "Stok batch '{$productBatch->batch_number}' tidak mencukupi untuk mengirim {$quantity} unit.",
            ]);
        }

        $productBatch->decrement('quantity', $quantity);
        $productStock->decrement('quantity', $quantity);

        $this->insertOutboundInventoryTransaction(deliveryOrder: $deliveryOrder, batch: $productBatch, productID: $productID, quantity: $quantity);
        // $this->recalculateMovingAverageCost(companyID: $deliveryOrder->company_id, productID: $productID, warehouseID: $deliveryOrder->warehouse_id);

        return $productBatch;
    }

    private function insertOutboundInventoryTransaction(DeliveryOrder $deliveryOrder, ProductBatch $batch, int $productID, float $quantity): void
    {
        $latestTransaction = InventoryTransaction::where('company_id', $deliveryOrder->company_id)
            ->where('product_id', $productID)
            ->where('warehouse_id', $deliveryOrder->warehouse_id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->first();

        $unitCost = ProductStock::where('company_id', $deliveryOrder->company_id)
            ->where('warehouse_id', $deliveryOrder->warehouse_id)
            ->where('product_id', $productID)
            ->value('average_unit_cost');

        $stockBefore = $latestTransaction ? $latestTransaction->stock_after : 0;

        InventoryTransaction::create([
            'company_id' => $deliveryOrder->company_id,
            'warehouse_id' => $deliveryOrder->warehouse_id,
            'product_id' => $productID,
            'product_batch_id' => $batch->id,
            'type' => InventoryTransactionTypeEnum::SALE,
            'direction' => -1,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'stock_before' => $stockBefore,
            'stock_after' => $stockBefore - $quantity,
            'reference_type' => DeliveryOrder::class,
            'reference_id' => $deliveryOrder->id,
            'transaction_date' => now(),
            'note' => 'Pengiriman Barang dari DO #' . $deliveryOrder->number,
        ]);
    }

    public function updateUnitCostFromPI(PurchaseInvoice $pi, Collection $piItems, float $newInventoryValue, float $totalQty): void
    {
        $grItemIDs = $piItems->pluck('goods_receipt_item_id')->toArray();
        $oldBatches = ProductBatch::where('company_id', $pi->company_id)
            ->where('warehouse_id', $pi->warehouse_id)
            ->whereIn('goods_receipt_item_id', $grItemIDs)
            ->get();

        $oldInventoryValue = 0;
        foreach ($oldBatches as $batch) {
            $oldInventoryValue += $batch->quantity * $batch->unit_cost;
        }
        $valueDifference = $newInventoryValue - $oldInventoryValue;

        foreach ($oldBatches as $batch) {
            $ratio = $batch->quantity / $totalQty;
            $adjustment = ($valueDifference * $ratio) / $batch->quantity;
            $unitCost = $batch->unit_cost + $adjustment;
            $batch->update(['unit_cost' => $unitCost]);

            $latestTransaction = InventoryTransaction::where('company_id', $batch->company_id)
                ->where('product_id', $batch->product_id)
                ->where('warehouse_id', $batch->warehouse_id)
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->first();
            InventoryTransaction::create([
                'company_id' => $batch->company_id,
                'warehouse_id' => $batch->warehouse_id,
                'product_id' => $batch->product_id,
                'product_batch_id' => $batch->id,
                'type' => InventoryTransactionTypeEnum::COST_ADJUSTMENT,
                'direction' => 0,
                'quantity' => 0,
                'unit_cost' => $unitCost,
                'total_cost' => 0,
                'stock_before' => $latestTransaction ? $latestTransaction->stock_after : 0,
                'stock_after' => $latestTransaction ? $latestTransaction->stock_after : 0,
                'reference_type' => PurchaseInvoice::class,
                'reference_id' => $pi->id, // You can set this to a relevant ID if needed
                'transaction_date' => now(),
                'note' => 'Penyesuaian HPP dari PI #' . $pi->number,
            ]);

            $this->recalculateMovingAverageCost(companyID: $batch->company_id, productID: $batch->product_id, warehouseID: $batch->warehouse_id);
        }
    }

    private function insertInventoryTransaction(GoodsReceipt $goodsReceipt, GoodsReceiptItem $goodsReceiptItem, ProductBatch $batch): void
    {
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
    }

    private function recalculateMovingAverageCost(int $companyID, int $productID, int $warehouseID): float | int
    {
        $batches = ProductBatch::where('company_id', $companyID)
            ->where('product_id', $productID)
            ->where('warehouse_id', $warehouseID)
            ->where('quantity', '>', 0)
            ->orderByDesc('id')
            ->get();

        $totalQty = 0;
        $totalUnitCost = 0;
        foreach ($batches as $batch) {
            $totalQty += $batch->quantity;
            $totalUnitCost += $batch->unit_cost * $batch->quantity;
        }

        $avgUnitCost = $totalQty > 0 ? $totalUnitCost / $totalQty : 0;

        ProductStock::updateOrCreate(
            [
                'company_id' => $companyID,
                'product_id' => $productID,
                'warehouse_id' => $warehouseID,
            ],
            [
                'quantity' => $totalQty,
                'average_unit_cost' => $avgUnitCost,
            ]
        );

        return $avgUnitCost;
    }

    public function fetchQuantitySoldByProductThisMonth(int $companyID, int $productID): float
    {
        return DeliveryOrderItem::whereHas('deliveryOrder', function ($query) use ($companyID) {
            $query->where('company_id', $companyID)
                ->whereMonth('delivery_date', now()->month)
                ->whereYear('delivery_date', now()->year)
                ->where('status', DeliveryOrderStatus::FINISHED);
        })
            ->where('product_id', $productID)
            ->sum('quantity');
    }

    public function fetchQuantityReceivedByProductThisMonth(int $companyID, int $productID): float
    {
        return GoodsReceiptItem::whereHas('goodsReceipt', function ($query) use ($companyID) {
            $query->where('company_id', $companyID)
                ->whereMonth('receipt_date', now()->month)
                ->whereYear('receipt_date', now()->year)
                ->where('status', GoodsReceiptStatus::FINISHED);
        })
            ->where('product_id', $productID)
            ->sum('received_quantity');
    }
}
