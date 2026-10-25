<?php

namespace App\Http\Controllers\Master\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\WarehouseTransferFormRequest;
use App\Services\InventoryService;
use App\Services\Master\WarehouseService;
use App\Services\Master\WarehouseTransferService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class WarehouseTransferController extends Controller
{
    protected WarehouseService $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function create(WarehouseTransferService $warehouseTransferService, InventoryService $inventoryService, int $id)
    {
        $data = [
            'currentPage' => 'master',
            'breadcrumb' => [
                ['label' => 'Master', 'url' => route('master.index')],
                ['label' => 'Gudang', 'url' => route('master.warehouses.show', $id)],
                ['label' => 'Transfer Gudang'],
            ],
            'warehouse' => $this->warehouseService->fetchWarehouseByID($id),
            'warehouses' => $this->warehouseService->fetchWarehouseData(),
            'batches' => $inventoryService->fetchInventoryBatches(
                companyID: config('context.selected_company_id'),
                warehouseID: $id,
                onlyAvailable: true
            ),
            'number' => $warehouseTransferService->generateNumber(),
        ];

        return view('master.warehouse.warehouse-transfer.create', $data);
    }

    public function store(WarehouseTransferFormRequest $request, WarehouseTransferService $warehouseTransferService, int $id)
    {
        try {
            $warehouseTransferService->storeWarehouseTransfer($request, $id);

            return response()->json(['message' => 'Transfer gudang berhasil dibuat.', 'redirect' => route('master.warehouses.show', $id)]);
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error WarehouseTransferController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $this->loggableRequest($request),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat transfer gudang. Silakan coba lagi.'], 500);
        }
    }
}
