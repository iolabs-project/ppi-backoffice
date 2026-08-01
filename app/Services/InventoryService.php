<?php

namespace App\Services;

use App\Enums\AccountSettingEnum;
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

class InventoryService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }
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

        $this->insertInventoryTransaction(goodsReceipt: $goodsReceipt, goodsReceiptItem: $goodsReceiptItem, batch: $productBatch);
        $this->recalculateMovingAverageCost(companyID: $goodsReceipt->company_id, productID: $goodsReceiptItem->product_id, warehouseID: $goodsReceipt->warehouse_id);

        return $productBatch;
    }

    public function updateUnitCostFromPI(PurchaseInvoice $pi, PurchaseInvoiceItem $piItem, float $costAmount = 0, float $discountAmount = 0): void
    {
        $grItem = GoodsReceiptItem::find($piItem->goods_receipt_item_id);
        $grItemUnitPrice = $grItem->unit_price - ($grItem->received_quantity > 0 ? $grItem->discount_amount / $grItem->received_quantity : 0);
        $piItemUnitPrice = $piItem->unit_price - ($piItem->quantity > 0 ? $piItem->discount_amount / $piItem->quantity : 0);
        $priceDifference = $piItemUnitPrice - $grItemUnitPrice;
        if ($priceDifference != 0 || $costAmount != 0) {
            $unitCost = $grItem->unit_cost + $priceDifference + $costAmount - $discountAmount;

            $batch = ProductBatch::where('company_id', $pi->company_id)
                ->where('product_id', $piItem->product_id)
                ->where('warehouse_id', $pi->warehouse_id)
                ->where('goods_receipt_item_id', $grItem->id)
                ->first();
            $batch?->update(['unit_cost' => $unitCost]);

            $avgUnitCost = $this->recalculateMovingAverageCost(companyID: $pi->company_id, productID: $batch->product_id, warehouseID: $batch->warehouse_id);

            $latestTransaction = InventoryTransaction::where('company_id', $pi->company_id)
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
                'type' => 'cost_adjustment',
                'direction' => 0,
                'quantity' => 0,
                'unit_cost' => $avgUnitCost,
                'total_cost' => 0,
                'stock_before' => $latestTransaction ? $latestTransaction->stock_after : 0,
                'stock_after' => $latestTransaction ? $latestTransaction->stock_after : 0,
                'reference_type' => PurchaseInvoiceItem::class,
                'reference_id' => $piItem->id,
                'transaction_date' => now(),
                'note' => 'Penyesuaian Harga Unit dari PI #' . $piItem->purchase_invoice_number,
            ]);
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

        // ProductStock::where('company_id', $companyID)
        //     ->where('product_id', $productID)
        //     ->where('warehouse_id', $warehouseID)
        //     ->update([
        //         'average_unit_cost' => $avgUnitCost,
        //     ]);

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
