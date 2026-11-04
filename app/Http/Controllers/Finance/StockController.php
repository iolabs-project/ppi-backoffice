<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class StockController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $data = [
            'currentPage' => 'finance.stock',
            'breadcrumb'  => [['label' => 'Stok']],
            'summary'     => $this->inventoryService->fetchStockSummary(companyID: config('context.selected_company_id')),
        ];
        return view('finance.stock.index', $data);
    }

    public function datatable(Request $request)
    {
        try {
            $data = $this->inventoryService->fetchStockSummaryTableData(
                companyID: config('context.selected_company_id'),
                perPage: $request->input('per_page', 10),
                search: $request->input('search', null)
            );

            return response()->json($data);
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error Finance/StockController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $this->loggableRequest($request),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data stok',
            ], 500);
        }
    }
}
