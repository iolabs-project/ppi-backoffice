<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\WarehouseFormRequest;
use App\Services\Master\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    public function datatable(Request $request, WarehouseService $warehouseService)
    {
        try {
            $data = $warehouseService->fetchWarehouseTableData($request);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/WarehouseController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data gudang',
            ], 500);
        }
    }

    public function store(WarehouseFormRequest $request, WarehouseService $warehouseService)
    {
        try {
            $warehouseService->storeWarehouse($request);

            return response()->json([
                'message' => 'Gudang berhasil dibuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Error WarehouseController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat gudang',
            ], 500);
        }
    }

    public function update(WarehouseFormRequest $request, WarehouseService $warehouseService, int $id)
    {
        try {
            $warehouseService->updateWarehouse($request, $id);

            return response()->json([
                'message' => 'Gudang berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error WarehouseController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui gudang',
            ], 500);
        }
    }

    public function status(Request $request, WarehouseService $warehouseService, int $id)
    {
        try {
            $warehouseService->toggleWarehouseStatus($id);

            return response()->json([
                'message' => 'Status gudang berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error WarehouseController@status: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui status gudang',
            ], 500);
        }
    }
    public function options(Request $request, WarehouseService $warehouseService)
    {
        try {
            $data = $warehouseService->fetchOptionData($request);

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error WarehouseController@options: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data gudang',
            ], 500);
        }
    }
}
