<?php

namespace App\Services\Master;

use App\Enums\AccountSettingEnum;
use App\Models\AccountSetting;
use App\Models\Warehouse;
use App\Models\Company;
use App\Models\ProductBatch;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Services\InventoryService;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    protected InventoryService $inventoryService;
    protected JournalService $journalService;

    public function __construct(InventoryService $inventoryService, JournalService $journalService)
    {
        $this->inventoryService = $inventoryService;
        $this->journalService = $journalService;
    }

    public function generateNumber(): string
    {
        $prefix = 'SA';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = StockAdjustment::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function storeStockAdjustment(Request $request, int $warehouseID): void
    {
        $companyID = config('context.selected_company_id');
        $detailsCollection = collect($request->input('details', []));

        DB::transaction(function () use ($request, $companyID, $warehouseID, $detailsCollection) {
            $stockAdjustment = StockAdjustment::create([
                'company_id' => $companyID,
                'warehouse_id' => $warehouseID,
                'number' => $this->generateNumber(),
                'adjustment_date' => $request->input('adjustment_date'),
                'status' => 'finished',
                'note' => $request->input('note'),
                'created_by' => Auth::id(),
            ]);

            foreach ($detailsCollection as $item) {
                $productBatch = ProductBatch::where('id', $item['product_batch_id'])
                    ->where('warehouse_id', $warehouseID)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$productBatch) {
                    throw ValidationException::withMessages([
                        'details' => "Batch tidak ditemukan atau bukan milik produk/gudang ini.",
                    ]);
                }

                $systemQuantity = (float) $productBatch->quantity;
                $countedQuantity = (float) ($item['counted_quantity'] ?? 0);

                $this->inventoryService->adjustInventoryFromStockAdjustment(
                    stockAdjustment: $stockAdjustment,
                    productID: (int) $item['product_id'],
                    productBatchID: (int) $item['product_batch_id'],
                    systemQuantity: $systemQuantity,
                    countedQuantity: $countedQuantity,
                );

                $stockAdjustmentItem = StockAdjustmentItem::create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'product_id' => $item['product_id'],
                    'product_batch_id' => $item['product_batch_id'],
                    'system_quantity' => $systemQuantity,
                    'counted_quantity' => $countedQuantity,
                    'unit_cost' => $productBatch->unit_cost,
                ]);

                $this->postSAJournal($stockAdjustment, $stockAdjustmentItem);
            }
        });
    }

    private function postSAJournal(StockAdjustment $stockAdjustment, StockAdjustmentItem $stockAdjustmentItem): void
    {
        if ($stockAdjustmentItem->counted_quantity > $stockAdjustmentItem->system_quantity) {
            $inventoryAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
                ->where('setting_key', AccountSettingEnum::INVENTORY->value)
                ->value('account_id');
            $inventoryGainAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
                ->where('setting_key', AccountSettingEnum::INVENTORY_ADJUSTMENT_GAIN->value)
                ->value('account_id');
            $amount = ($stockAdjustmentItem->counted_quantity - $stockAdjustmentItem->system_quantity) * $stockAdjustmentItem->unit_cost;

            $this->journalService->post(
                date: null,
                referenceType: StockAdjustment::class,
                referenceID: $stockAdjustment->id,
                description: 'Penyesuaian Stock # ' . $stockAdjustment->number,
                items: [
                    [
                        'account_id' => $inventoryAccountID,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $inventoryGainAccountID,
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            );
        } elseif ($stockAdjustmentItem->counted_quantity < $stockAdjustmentItem->system_quantity) {
            $inventoryAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
                ->where('setting_key', AccountSettingEnum::INVENTORY->value)
                ->value('account_id');
            $inventoryLossAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
                ->where('setting_key', AccountSettingEnum::INVENTORY_ADJUSTMENT_LOSS->value)
                ->value('account_id');
            $amount = ($stockAdjustmentItem->system_quantity - $stockAdjustmentItem->counted_quantity) * $stockAdjustmentItem->unit_cost;

            $this->journalService->post(
                date: null,
                referenceType: StockAdjustment::class,
                referenceID: $stockAdjustment->id,
                description: 'Penyesuaian Stock # ' . $stockAdjustment->number,
                items: [
                    [
                        'account_id' => $inventoryLossAccountID,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $inventoryAccountID,
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            );
        }
    }
}
