<?php

namespace App\Services\Master;

use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use App\Services\InventoryService;

class WarehouseTransferService
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function generateNumber(): string
    {
        $prefix = 'WT';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = WarehouseTransfer::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function storeWarehouseTransfer(Request $request, int $fromWarehouseID): void
    {
        $toWarehouseID = (int) $request->input('to_warehouse_id');

        if ($toWarehouseID === $fromWarehouseID) {
            throw ValidationException::withMessages([
                'to_warehouse_id' => 'Gudang tujuan tidak boleh sama dengan gudang asal.',
            ]);
        }

        $companyID = config('context.selected_company_id');
        $detailsCollection = collect($request->input('details', []));

        DB::transaction(function () use ($request, $companyID, $fromWarehouseID, $toWarehouseID, $detailsCollection) {
            $warehouseTransfer = WarehouseTransfer::create([
                'company_id' => $companyID,
                'from_warehouse_id' => $fromWarehouseID,
                'to_warehouse_id' => $toWarehouseID,
                'number' => $this->generateNumber(),
                'transfer_date' => $request->input('transfer_date'),
                'status' => 'finished',
                'note' => $request->input('note'),
                'created_by' => Auth::id(),
            ]);

            foreach ($detailsCollection as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);

                $result = $this->inventoryService->transferInventoryBetweenWarehouses(
                    warehouseTransfer: $warehouseTransfer,
                    productID: (int) $item['product_id'],
                    fromProductBatchID: (int) $item['product_batch_id'],
                    toWarehouseID: $toWarehouseID,
                    quantity: $quantity,
                );

                WarehouseTransferItem::create([
                    'warehouse_transfer_id' => $warehouseTransfer->id,
                    'product_id' => $item['product_id'],
                    'product_batch_id' => $item['product_batch_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $result['unit_cost'],
                ]);
            }

        });
    }
}
