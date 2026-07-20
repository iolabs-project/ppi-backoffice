<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Finance\AccountReceivableService;
use App\Services\Sales\SalesInvoiceService;

class AccountReceivableController extends Controller
{
    private AccountReceivableService $accountReceivableService;
    private SalesInvoiceService $salesInvoiceService;

    public function __construct(AccountReceivableService $accountReceivableService, SalesInvoiceService $salesInvoiceService)
    {
        $this->accountReceivableService = $accountReceivableService;
        $this->salesInvoiceService = $salesInvoiceService;
    }

   public function index()
    {
        $data = [
            'currentPage'    => 'finance',
            'breadcrumb'     => [['label' => 'Piutang']],
        ];
        return view('finance.account-receivable.index', $data);
    }

    public function datatable(Request $request)
    {
        try {
            $data = $this->accountReceivableService->fetchARTableData($request);
            return response()->json($data);
         } catch (\Exception $e) {
            Log::error('Error AccountReceivableController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba mengambil data Piutang. Silakan coba lagi.'], 500);
        }
    }

    public function show(int $id)
    {
        $salesInvoice = $this->salesInvoiceService->fetchSalesInvoiceByID($id);
        $data = [
            'currentPage'    => 'finance',
            'breadcrumb'     => [['label' => 'Piutang', 'url' => route('account_receivables.index')], ['label' => 'Detail']],
            'salesInvoice' => $salesInvoice,
        ];
        return view('finance.account-receivable.show', $data);
    }

    public function store(Request $request, int $id)
    {
        $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:1000',
        ]);
        try {
            $this->accountReceivableService->storePayment($request, $id);
            return response()->json(['redirect' => route('account_receivables.show', $id), 'message' => 'Pembayaran berhasil ditambahkan.']);
        } catch (\Exception $e) {
            Log::error('Error AccountReceivableController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menambahkan pembayaran. Silakan coba lagi.'], 500);
        }
    }
}
