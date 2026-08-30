<?php

namespace App\Http\Controllers\Master\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\WarehouseFormRequest;
use App\Services\InventoryService;
use App\Services\Master\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    protected WarehouseService $warehouseService;
    protected InventoryService $inventoryService;
    public function __construct(WarehouseService $warehouseService, InventoryService $inventoryService)
    {
        $this->warehouseService = $warehouseService;
        $this->inventoryService = $inventoryService;
    }
    public function datatable(Request $request)
    {
        try {
            $data = $this->warehouseService->fetchWarehouseTableData($request);

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

    public function datatableStock(Request $request)
    {
        try {
            $data = $this->inventoryService->fetchInventoryStockTableData(
                companyID: config('context.selected_company_id'),
                warehouseID: $request->input('warehouse_id'),
                perPage: $request->input('per_page', 10),
                search: $request->input('search', null)
            );

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/WarehouseController@datatableStock: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data stock gudang',
            ], 500);
        }
    }

    public function datatableBatch(Request $request)
    {
        try {
            $data = $this->inventoryService->fetchInventoryBatchTableData(
                companyID: config('context.selected_company_id'),
                warehouseID: $request->input('warehouse_id'),
                perPage: $request->input('per_page', 10),
                onlyAvailable: $request->has('only_available') ? $request->boolean('only_available') : null,
                search: $request->input('search', null)
            );

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/WarehouseController@datatableBatch: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data batch gudang',
            ], 500);
        }
    }

    public function store(WarehouseFormRequest $request)
    {
        try {
            $this->warehouseService->storeWarehouse($request);

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

    public function show(int $id)
    {
        $data = [
            'currentPage'          => 'master',
            'breadcrumb'           => [['label' => 'Master Gudang']],
            'warehouse' => $this->warehouseService->fetchWarehouseById($id),
            'products' => $this->inventoryService->fetchInventoryStock(companyID: config('context.selected_company_id'), warehouseID: $id),
            'batches' => $this->inventoryService->fetchInventoryBatches(companyID: config('context.selected_company_id'), warehouseID: $id),
        ];

        return view('master.warehouse.show', $data);
    }

    public function update(WarehouseFormRequest $request, int $id)
    {
        try {
            $this->warehouseService->updateWarehouse($request, $id);

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

    public function status(Request $request, int $id)
    {
        try {
            $this->warehouseService->toggleWarehouseStatus($id);

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
    public function options(Request $request)
    {
        try {
            $data = $this->warehouseService->fetchOptionData($request);

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
