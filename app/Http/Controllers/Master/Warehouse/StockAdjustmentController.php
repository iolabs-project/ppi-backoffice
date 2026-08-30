<?php

namespace App\Http\Controllers\Master\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StockAdjustmentFormRequest;
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

    public function store(StockAdjustmentFormRequest $request, StockAdjustmentService $stockAdjustmentService, int $id)
    {
        try {
            $stockAdjustment = $stockAdjustmentService->storeStockAdjustment($request, $id);

            return response()->json(['message' => 'Penyesuaian stok berhasil dibuat.', 'data' => $stockAdjustment]);
        } catch (ValidationException $e) {
            Log::error('Error StockAdjustmentController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
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
