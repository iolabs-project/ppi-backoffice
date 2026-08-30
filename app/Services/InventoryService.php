<?php

namespace App\Services;

use App\Enums\DeliveryOrderStatus;
use App\Enums\GoodsReceiptStatus;
use App\Enums\InventoryTransactionTypeEnum;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\GoodsReceipt;
use App\Models\ProductBatch;
use App\Models\ProductBatchStock;
use App\Models\ProductStock;
use App\Models\InventoryTransaction;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\SalesInvoiceItem;
use App\Models\StockAdjustment;
use App\Models\WarehouseTransfer;
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
        return $this->buildInventoryBatchStockQuery($companyID, $warehouseID, $productIDs, $onlyAvailable)
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn($stock) => $this->transformBatchStock($stock));
    }

    public function fetchInventoryBatchTableData(int $companyID, int $perPage, int | null $warehouseID = null, array $productIDs = [], bool | null $onlyAvailable = null, string | null $search = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->buildInventoryBatchStockQuery($companyID, $warehouseID, $productIDs, $onlyAvailable)
            ->when($search !== null, function ($query) use ($search) {
                $query->whereHas('productBatch.product', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->through(fn($stock) => $this->transformBatchStock($stock));
    }

    private function buildInventoryBatchStockQuery(int $companyID, int | null $warehouseID, array $productIDs, bool | null $onlyAvailable)
    {
        return ProductBatchStock::with([
            'productBatch:id,company_id,product_id,batch_number,initial_quantity,unit_cost',
            'productBatch.product:id,code,name,unit_id',
            'productBatch.product.unit:id,name,symbol',
            'warehouse:id,name',
        ])
            ->whereHas('productBatch', function ($query) use ($companyID, $productIDs) {
                $query->where('company_id', $companyID)
                    ->whereHas('product', function ($query) {
                        $query->whereNull('deleted_at');
                    })
                    ->when(!empty($productIDs), function ($query) use ($productIDs) {
                        $query->whereIn('product_id', $productIDs);
                    });
            })
            ->when($warehouseID !== null, function ($query) use ($warehouseID) {
                $query->where('warehouse_id', $warehouseID);
            })
            ->when($onlyAvailable !== null, function ($query) use ($onlyAvailable) {
                if ($onlyAvailable) {
                    $query->whereColumn('quantity', '>', 'reserved_quantity');
                } else {
                    $query->whereColumn('quantity', '<=', 'reserved_quantity');
                }
            });
    }

    private function transformBatchStock(ProductBatchStock $stock): array
    {
        return [
            'id' => $stock->product_batch_id,
            'company_id' => $stock->productBatch->company_id,
            'warehouse_id' => $stock->warehouse_id,
            'product_id' => $stock->productBatch->product_id,
            'batch_number' => $stock->productBatch->batch_number,
            'initial_quantity' => $stock->productBatch->initial_quantity,
            'quantity' => $stock->quantity,
            'reserved_quantity' => $stock->reserved_quantity,
            'available_quantity' => $stock->available_quantity,
            'unit_cost' => $stock->productBatch->unit_cost,
            'product' => $stock->productBatch->product,
            'warehouse' => $stock->warehouse,
        ];
    }

    public function receiveInventoryFromGR(GoodsReceipt $goodsReceipt, GoodsReceiptItem $goodsReceiptItem): ProductBatch
    {
        $productBatch = ProductBatch::create([
            'company_id' => $goodsReceipt->company_id,
            'product_id' => $goodsReceiptItem->product_id,
            'goods_receipt_item_id' => $goodsReceiptItem->id,
            'batch_number' => $goodsReceiptItem->batch_number,
            'initial_quantity' => $goodsReceiptItem->received_quantity,
            'unit_cost' => $goodsReceiptItem->unit_cost,
        ]);

        ProductBatchStock::create([
            'product_batch_id' => $productBatch->id,
            'warehouse_id' => $goodsReceipt->warehouse_id,
            'quantity' => $goodsReceiptItem->received_quantity,
            'reserved_quantity' => 0,
        ]);

        // $this->insertInventoryTransactionFromGR(goodsReceipt: $goodsReceipt, goodsReceiptItem: $goodsReceiptItem, batch: $productBatch);
        $this->insertInventoryTransaction(
            companyID: $goodsReceipt->company_id,
            warehouseID: $goodsReceipt->warehouse_id,
            productID: $goodsReceiptItem->product_id,
            productBatchID: $productBatch->id,
            type: InventoryTransactionTypeEnum::PURCHASE->value,
            direction: 1,
            quantity: $goodsReceiptItem->received_quantity,
            unitCost: $goodsReceiptItem->unit_cost,
            referenceType: GoodsReceipt::class,
            referenceID: $goodsReceipt->id,
            transactionDate: now(),
            note: 'Penerimaan Barang dari GR #' . $goodsReceipt->number,
        );
        $this->recalculateMovingAverageCost(companyID: $goodsReceipt->company_id, productID: $goodsReceiptItem->product_id, warehouseID: $goodsReceipt->warehouse_id);

        return $productBatch;
    }
    public function issueInventoryFromDO(DeliveryOrder $deliveryOrder, int $productID, int $productBatchID, float $quantity, ?string $batchNumberHint = null): ProductBatch
    {
        $productBatch = ProductBatch::where('id', $productBatchID)
            ->where('product_id', $productID)
            ->first();

        if (!$productBatch) {
            throw ValidationException::withMessages([
                'details' => "Batch '{$batchNumberHint}' tidak ditemukan atau bukan milik produk ini.",
            ]);
        }

        $productBatchStock = ProductBatchStock::where('product_batch_id', $productBatch->id)
            ->where('warehouse_id', $deliveryOrder->warehouse_id)
            ->lockForUpdate()
            ->first();

        $productStock = ProductStock::where('company_id', $deliveryOrder->company_id)
            ->where('warehouse_id', $deliveryOrder->warehouse_id)
            ->where('product_id', $productID)
            ->lockForUpdate()
            ->first();

        if (!$productBatchStock || $productBatchStock->available_quantity < $quantity) {
            throw ValidationException::withMessages([
                'details' => "Stok batch '{$productBatch->batch_number}' tidak mencukupi untuk mengirim {$quantity} unit.",
            ]);
        }

        $productBatchStock->decrement('quantity', $quantity);
        $productStock->decrement('quantity', $quantity);

        $this->insertInventoryTransaction(
            companyID: $deliveryOrder->company_id,
            warehouseID: $deliveryOrder->warehouse_id,
            productID: $productID,
            productBatchID: $productBatch->id,
            type: InventoryTransactionTypeEnum::SALE->value,
            direction: -1,
            quantity: $quantity,
            unitCost: $productStock->average_unit_cost,
            referenceType: DeliveryOrder::class,
            referenceID: $deliveryOrder->id,
            transactionDate: now(),
            note: 'Pengiriman Barang dari DO #' . $deliveryOrder->number,
        );

        return $productBatch;
    }

    public function transferInventoryBetweenWarehouses(WarehouseTransfer $warehouseTransfer, int $productID, int $fromProductBatchID, int $toWarehouseID, float $quantity): array
    {
        $fromWarehouseID = $warehouseTransfer->from_warehouse_id;

        $batch = ProductBatch::where('id', $fromProductBatchID)
            ->where('product_id', $productID)
            ->first();

        if (!$batch) {
            throw ValidationException::withMessages([
                'details' => "Batch tidak ditemukan atau bukan milik produk ini.",
            ]);
        }

        $fromStock = ProductBatchStock::where('product_batch_id', $batch->id)
            ->where('warehouse_id', $fromWarehouseID)
            ->lockForUpdate()
            ->first();

        if (!$fromStock || $fromStock->available_quantity < $quantity) {
            throw ValidationException::withMessages([
                'details' => "Stok batch '{$batch->batch_number}' tidak mencukupi untuk transfer {$quantity} unit.",
            ]);
        }

        $fromStock->decrement('quantity', $quantity);

        $this->insertInventoryTransaction(
            companyID: $warehouseTransfer->company_id,
            warehouseID: $fromWarehouseID,
            productID: $productID,
            productBatchID: $batch->id,
            type: InventoryTransactionTypeEnum::TRANSFER_OUT->value,
            direction: -1,
            quantity: $quantity,
            unitCost: $batch->unit_cost,
            referenceType: WarehouseTransfer::class,
            referenceID: $warehouseTransfer->id,
            // transactionDate: $warehouseTransfer->transfer_date,
            transactionDate: now()->format('Y-m-d H:i:s'),
            note: 'Transfer keluar ke gudang lain #' . $warehouseTransfer->number,
        );

        // the same batch may already hold stock in the destination warehouse
        $toStock = ProductBatchStock::where('product_batch_id', $batch->id)
            ->where('warehouse_id', $toWarehouseID)
            ->lockForUpdate()
            ->first();

        if ($toStock) {
            $toStock->increment('quantity', $quantity);
        } else {
            ProductBatchStock::create([
                'product_batch_id' => $batch->id,
                'warehouse_id' => $toWarehouseID,
                'quantity' => $quantity,
                'reserved_quantity' => 0,
            ]);
        }

        $this->insertInventoryTransaction(
            companyID: $warehouseTransfer->company_id,
            warehouseID: $toWarehouseID,
            productID: $productID,
            productBatchID: $batch->id,
            type: InventoryTransactionTypeEnum::TRANSFER_IN->value,
            direction: 1,
            quantity: $quantity,
            unitCost: $batch->unit_cost,
            referenceType: WarehouseTransfer::class,
            referenceID: $warehouseTransfer->id,
            // transactionDate: $warehouseTransfer->transfer_date,
            transactionDate: now()->format('Y-m-d H:i:s'),
            note: 'Transfer masuk dari gudang lain #' . $warehouseTransfer->number,
        );

        $this->recalculateMovingAverageCost(companyID: $warehouseTransfer->company_id, productID: $productID, warehouseID: $fromWarehouseID);
        $this->recalculateMovingAverageCost(companyID: $warehouseTransfer->company_id, productID: $productID, warehouseID: $toWarehouseID);

        return [
            'batch' => $batch,
            'unit_cost' => $batch->unit_cost,
        ];
    }

    public function adjustInventoryFromStockAdjustment(StockAdjustment $stockAdjustment, int $productID, int $productBatchID, float $systemQuantity, float $countedQuantity): ProductBatch
    {
        $productBatch = ProductBatch::where('id', $productBatchID)
            ->where('product_id', $productID)
            ->first();

        if (!$productBatch) {
            throw ValidationException::withMessages([
                'details' => "Batch tidak ditemukan atau bukan milik produk ini.",
            ]);
        }

        $productBatchStock = ProductBatchStock::where('product_batch_id', $productBatch->id)
            ->where('warehouse_id', $stockAdjustment->warehouse_id)
            ->lockForUpdate()
            ->first();

        if (!$productBatchStock) {
            throw ValidationException::withMessages([
                'details' => "Batch tidak ditemukan atau bukan milik gudang ini.",
            ]);
        }

        $differenceQuantity = $countedQuantity - $systemQuantity;

        if ($differenceQuantity == 0) {
            return $productBatch;
        }

        $productBatchStock->update(['quantity' => $countedQuantity]);

        $this->insertInventoryTransaction(
            companyID: $stockAdjustment->company_id,
            warehouseID: $stockAdjustment->warehouse_id,
            productID: $productID,
            productBatchID: $productBatch->id,
            type: $differenceQuantity > 0
                ? InventoryTransactionTypeEnum::ADJUSTMENT_PLUS->value
                : InventoryTransactionTypeEnum::ADJUSTMENT_MINUS->value,
            direction: $differenceQuantity > 0 ? 1 : -1,
            quantity: abs($differenceQuantity),
            unitCost: $productBatch->unit_cost,
            referenceType: StockAdjustment::class,
            referenceID: $stockAdjustment->id,
            transactionDate: $stockAdjustment->adjustment_date,
            note: 'Penyesuaian Stok #' . $stockAdjustment->number,
        );

        $this->recalculateMovingAverageCost(companyID: $stockAdjustment->company_id, productID: $productID, warehouseID: $stockAdjustment->warehouse_id);

        return $productBatch;
    }

    public function insertInventoryTransaction(
        int $companyID,
        int $warehouseID,
        int $productID,
        int $productBatchID,
        string $type,
        int $direction,
        float $quantity,
        ?float $unitCost,
        string $referenceType,
        int $referenceID,
        string $transactionDate,
        ?string $note = null,
    ): InventoryTransaction {
        $latestTransaction = InventoryTransaction::where('company_id', $companyID)
            ->where('product_id', $productID)
            ->where('product_batch_id', $productBatchID)
            ->where('warehouse_id', $warehouseID)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->first();

        $stockBefore = $latestTransaction?->stock_after ?? 0;
        $stockAfter = $stockBefore + ($quantity * $direction);

        return InventoryTransaction::create([
            'company_id' => $companyID,
            'warehouse_id' => $warehouseID,
            'product_id' => $productID,
            'product_batch_id' => $productBatchID,
            'type' => $type,
            'direction' => $direction,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost !== null
                ? $unitCost * $quantity
                : null,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceID,
            'transaction_date' => $transactionDate,
            'note' => $note,
        ]);
    }

    public function updateUnitCostFromPI(PurchaseInvoice $pi, Collection $piItems, float $newInventoryValue, float $totalQty): void
    {
        $grItemIDs = $piItems->pluck('goods_receipt_item_id')->toArray();
        $oldBatches = ProductBatch::with('productBatchStocks')
            ->where('company_id', $pi->company_id)
            ->whereIn('goods_receipt_item_id', $grItemIDs)
            ->get();

        $oldInventoryValue = 0;
        foreach ($oldBatches as $batch) {
            $oldInventoryValue += $batch->productBatchStocks->sum('quantity') * $batch->unit_cost;
        }
        $valueDifference = $newInventoryValue - $oldInventoryValue;

        foreach ($oldBatches as $batch) {
            $batchQty = $batch->productBatchStocks->sum('quantity');
            $ratio = $batchQty / $totalQty;
            $adjustment = ($valueDifference * $ratio) / $batchQty;
            $unitCost = $batch->unit_cost + $adjustment;
            $batch->update(['unit_cost' => $unitCost]);

            foreach ($batch->productBatchStocks as $stock) {
                if ($stock->quantity <= 0) {
                    continue;
                }

                $latestTransaction = InventoryTransaction::where('company_id', $batch->company_id)
                    ->where('product_id', $batch->product_id)
                    ->where('warehouse_id', $stock->warehouse_id)
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->first();
                InventoryTransaction::create([
                    'company_id' => $batch->company_id,
                    'warehouse_id' => $stock->warehouse_id,
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

                $this->recalculateMovingAverageCost(companyID: $batch->company_id, productID: $batch->product_id, warehouseID: $stock->warehouse_id);
            }
        }
    }

    private function recalculateMovingAverageCost(int $companyID, int $productID, int $warehouseID): float | int
    {
        $stocks = ProductBatchStock::with('productBatch:id,unit_cost')
            ->whereHas('productBatch', function ($query) use ($companyID, $productID) {
                $query->where('company_id', $companyID)
                    ->where('product_id', $productID);
            })
            ->where('warehouse_id', $warehouseID)
            ->where('quantity', '>', 0)
            ->orderByDesc('id')
            ->get();

        $totalQty = 0;
        $totalUnitCost = 0;
        foreach ($stocks as $stock) {
            $totalQty += $stock->quantity;
            $totalUnitCost += $stock->productBatch->unit_cost * $stock->quantity;
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
