<?php

namespace App\Http\Controllers;

use App\Enums\ReportTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ReportService;

class ReportController extends Controller
{
    protected ReportService $reportService;
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    public function index()
    {
        return view('report.index', [
            'currentPage' => 'laporan',
            'breadcrumb'  => [['label' => 'Laporan']],
            'activeTab'   => null,
        ]);
    }

    public function show(string $id)
    {
        if (!ReportTypeEnum::exists($id)) {
            abort(404);
        }

        return view('report.show', [
            'currentPage' => 'laporan',
            'breadcrumb'  => [['label' => 'Laporan', 'url' => route('reports.index')], ['label' => ReportTypeEnum::from($id)->label()]],
            'activeTab'   => $id,
        ]);
    }

    public function profitLossDatatable(Request $request)
    {
        try {
            $data = $this->reportService->fetchProfitLossTableData($request, config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ReportController@profitLossDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data laporan laba rugi',
            ], 500);
        }
    }

    public function balanceSheetDatatable(Request $request)
    {
        try {
            $data = $this->reportService->fetchBalanceSheetData($request, config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ReportController@balanceSheetDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data neraca',
            ], 500);
        }
    }

    public function cashFlowDatatable(Request $request)
    {
        try {
            $data = $this->reportService->fetchCashFlowData($request, config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ReportController@cashFlowDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data arus kas',
            ], 500);
        }
    }

    public function executiveDatatable(Request $request)
    {
        try {
            $data = $this->reportService->fetchExecutiveSummaryData($request, config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ReportController@executiveDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data ringkasan eksekutif',
            ], 500);
        }
    }

    public function receivableDatatable(Request $request)
    {
        try {
            $data = $this->reportService->fetchReceivableReportData($request, config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ReportController@receivableDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data piutang',
            ], 500);
        }
    }

    public function payableDatatable(Request $request)
    {
        try {
            $data = $this->reportService->fetchPayableReportData($request, config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ReportController@payableDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data utang',
            ], 500);
        }
    }

    public function journalDatatable(Request $request)
    {
        try {
            $data = $this->reportService->fetchJournalTableData($request, config('context.selected_company_id'));

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error ReportController@journalDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data laporan jurnal',
            ], 500);
        }
    }
}
