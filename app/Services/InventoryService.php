<?php

namespace App\Services;

use App\Enums\AccountSettingEnum;
use App\Enums\InventoryTransactionTypeEnum;
use App\Models\AccountSetting;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\InventoryTransaction;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class InventoryService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }
    public function fetchGlobalInventoryStock(int $companyID): Collection
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
            ->whereRaw('quantity - reserved_quantity > 0')
            ->get();

        return $data;
    }

    public function fetchInventoryBatches(int $companyID): Collection
    {
        return ProductBatch::with([
            'product:id,code,name,unit_id',
            'product.unit:id,name,symbol',
            'warehouse:id,name',
        ])
            ->select(
                'company_id',
                'warehouse_id',
                'product_id',
                'batch_number',
                'quantity',
                'reserved_quantity',
                'unit_cost'
            )
            ->where('company_id', $companyID)
            ->whereRaw('quantity - reserved_quantity > 0')
            ->get();
    }

    public function receiveInventoryFromGR(GoodsReceipt $goodsReceipt, GoodsReceiptItem $goodsReceiptItem): ProductBatch
    {
        $productBatch = ProductBatch::create([
            'company_id' => $goodsReceipt->company_id,
            'warehouse_id' => $goodsReceipt->warehouse_id,
            'product_id' => $goodsReceiptItem->product_id,
            'goods_receipt_item_id' => $goodsReceiptItem->id,
            'batch_number' => $goodsReceiptItem->batch_number,
            'quantity' => $goodsReceiptItem->received_quantity,
            'unit_cost' => $goodsReceiptItem->unit_cost,
        ]);

        $this->insertInventoryTransaction(goodsReceipt: $goodsReceipt, goodsReceiptItem: $goodsReceiptItem, batch: $productBatch);
        $this->recalculateMovingAverageCost(companyID: $goodsReceipt->company_id, productID: $goodsReceiptItem->product_id, warehouseID: $goodsReceipt->warehouse_id);

        return $productBatch;
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
                'reference_type' => PurchaseInvoiceItem::class,
                'reference_id' => null, // You can set this to a relevant ID if needed
                'transaction_date' => now(),
                'note' => 'Penyesuaian HPP dari PI #' . $pi->number,
            ]);

            $this->recalculateMovingAverageCost(companyID: $batch->company_id, productID: $batch->product_id, warehouseID: $batch->warehouse_id);
        }
    }

    /**
     * Decrease stock quantity on a sale. WAC does not change on outgoing stock.
     */
    public function deductStockOnSale(int $companyId, int $productId, int $warehouseId, float $quantity): void
    {
        ProductStock::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->decrement('quantity', $quantity);
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
            'reference_type' => GoodsReceiptItem::class,
            'reference_id' => $goodsReceiptItem->id,
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
}
