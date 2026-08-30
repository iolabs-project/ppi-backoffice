<?php

namespace App\Http\Controllers\Master\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StockAdjustmentFormRequest;
use App\Services\InventoryService;
use App\Services\Master\WarehouseService;
use App\Services\Master\StockAdjustmentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    protected WarehouseService $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function create(StockAdjustmentService $stockAdjustmentService, InventoryService $inventoryService, int $id)
    {
        $data = [
            'currentPage' => 'master',
            'breadcrumb' => [
                ['label' => 'Master', 'url' => route('master.index')],
                ['label' => 'Gudang', 'url' => route('master.warehouses.show', $id)],
                ['label' => 'Penyesuaian Stok'],
            ],
            'warehouse' => $this->warehouseService->fetchWarehouseByID($id),
            'batches' => $inventoryService->fetchInventoryBatches(
                companyID: config('context.selected_company_id'),
                warehouseID: $id,
            ),
            'number' => $stockAdjustmentService->generateNumber(),
        ];

        return view('master.warehouse.stock-adjustment.create', $data);
    }

    public function store(StockAdjustmentFormRequest $request, StockAdjustmentService $stockAdjustmentService, int $id)
    {
        try {
            $stockAdjustmentService->storeStockAdjustment($request, $id);

            return response()->json(['message' => 'Penyesuaian stok berhasil dibuat.', 'redirect' => route('master.warehouses.show', $id)]);
        } catch (\Exception $e) {
            Log::error('Error StockAdjustmentController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat penyesuaian stok. Silakan coba lagi.'], 500);
        }
    }
}
