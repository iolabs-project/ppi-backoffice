<?php

namespace App\Http\Controllers\Master\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\WarehouseTransferFormRequest;
use App\Services\Master\WarehouseService;
use App\Services\Master\WarehouseTransferService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WarehouseTransferController extends Controller
{
    protected WarehouseService $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function store(WarehouseTransferFormRequest $request, WarehouseTransferService $warehouseTransferService, int $id)
    {
        try {
            $warehouseTransfer = $warehouseTransferService->storeWarehouseTransfer($request, $id);

            return response()->json(['message' => 'Transfer gudang berhasil dibuat.', 'data' => $warehouseTransfer]);
        } catch (ValidationException $e) {
            Log::error('Error WarehouseTransferController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error WarehouseTransferController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat transfer gudang. Silakan coba lagi.'], 500);
        }
    }
}
