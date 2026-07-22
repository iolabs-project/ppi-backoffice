<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Enums\AccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\Finance\AccountReceivableService;
use App\Services\Sales\SalesInvoiceService;
use App\Services\Master\AccountService;

class AccountReceivableController extends Controller
{
    private AccountReceivableService $accountReceivableService;
    private SalesInvoiceService $salesInvoiceService;
    private AccountService $accountService;

    public function __construct(AccountReceivableService $accountReceivableService, SalesInvoiceService $salesInvoiceService, AccountService $accountService)
    {
        $this->accountReceivableService = $accountReceivableService;
        $this->salesInvoiceService = $salesInvoiceService;
        $this->accountService = $accountService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'finance.account_receivable',
            'breadcrumb'     => [['label' => 'Piutang']],
            'status' => [
                ['id' => 'all', 'name' => 'Semua'],
                ['id' => 'not-yet-due', 'name' => 'Not Yet Due'],
                ['id' => 'unpaid', 'name' => 'Unpaid'],
                ['id' => 'partial', 'name' => 'Partial'],
                ['id' => 'paid', 'name' => 'Paid'],
            ],
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
            'currentPage'    => 'finance.account_receivable',
            'breadcrumb'     => [['label' => 'Piutang', 'url' => route('finances.account_receivables.index')], ['label' => $salesInvoice->number ?? 'Detail']],
            'salesInvoice' => $salesInvoice,
            'displayStatus' => $salesInvoice ? $this->accountReceivableService->derivePaymentStatus($salesInvoice->status, $salesInvoice->due_date) : null,
            'cashBankAccounts' => $this->accountService->fetchAccountData(AccountCategory::CASH_BANK->value),
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
            return response()->json(['redirect' => route('finances.account_receivables.show', $id), 'message' => 'Pembayaran berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
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
