<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\InventoryTransaction;
use App\Models\GoodsReceiptItem;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function fetchGlobalInventoryStock()
    {
        $data = ProductBatch::with([
            'product:id,code,name,unit_id',
            'product.unit:id,name,symbol',
        ])
            ->select(
                'company_id',
                'product_id',
                'warehouse_id',
                // 'quantity',
                // 'reserved_quantity',
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(reserved_quantity) as reserved_quantity'),
                DB::raw('SUM(quantity * unit_cost) / NULLIF(SUM(quantity), 0) as avg_unit_cost'),
            )
            ->where('company_id', config('context.selected_company_id'))
            ->groupBy('product_id', 'company_id', 'warehouse_id')
            ->havingRaw('quantity - reserved_quantity > 0')
            ->get();

        return $data;
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

        $this->insertInventoryTransaction($goodsReceipt, $goodsReceiptItem, $productBatch);
        $this->recalculateAVGUnitCost($goodsReceipt->company_id, $goodsReceiptItem->product_id, $goodsReceipt->warehouse_id);

        return $productBatch;
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

    /**
     * Update a batch's unit_cost after a Purchase Invoice adjusts the price or adds landed costs,
     * then recalculate the WAC and propagate it to all batches for that product+warehouse.
     */
    public function adjustBatchUnitCost(ProductBatch $batch, float $newUnitCost): void
    {
        // Set the PI-corrected cost on this specific batch before WAC is recomputed
        $batch->update(['unit_cost' => $newUnitCost]);

        // Recalculate WAC from all batches (including the updated one) and propagate to all
        $this->recalculateAVGUnitCost($batch->company_id, $batch->product_id, $batch->warehouse_id);
    }

    /**
     * Recompute WAC from all product_batches for a given product+warehouse,
     * update every batch's unit_cost to the new average,
     * and upsert the result into product_stocks.
     */
    public function recalculateAVGUnitCost(int $companyId, int $productId, int $warehouseId): void
    {
        $aggregate = ProductBatch::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->selectRaw('SUM(quantity) as total_quantity, SUM(reserved_quantity) as total_reserved, SUM(quantity * unit_cost) as total_value')
            ->first();

        $totalQty      = (float) ($aggregate->total_quantity ?? 0);
        $totalReserved = (float) ($aggregate->total_reserved ?? 0);
        $totalValue    = (float) ($aggregate->total_value ?? 0);
        $avgUnitCost   = $totalQty > 0 ? $totalValue / $totalQty : 0;

        // Propagate the new average back to every batch for this product+warehouse
        ProductBatch::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->update(['unit_cost' => $avgUnitCost]);

        // Also update the corresponding purchase inventory transactions
        $batchIds = ProductBatch::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->pluck('id');

        InventoryTransaction::whereIn('product_batch_id', $batchIds)
            ->where('type', 'purchase')
            ->each(function (InventoryTransaction $tx) use ($avgUnitCost) {
                $tx->update([
                    'unit_cost'  => $avgUnitCost,
                    'total_cost' => $avgUnitCost * $tx->quantity,
                ]);
            });

        ProductStock::updateOrCreate(
            [
                'company_id'   => $companyId,
                'product_id'   => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity'          => $totalQty,
                'reserved_quantity' => $totalReserved,
                'average_unit_cost' => $avgUnitCost,
            ]
        );
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
}
