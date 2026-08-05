<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Enums\AccountCategory;
use App\Enums\AccountReceivableStatusEnum;
use App\Http\Requests\Finance\AccountReceivableFormRequest;
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
            'status' => AccountReceivableStatusEnum::dropdownOptions(),
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

    public function paymentDatatable(Request $request)
    {
        try {
            $data = $this->accountReceivableService->fetchPaymentTableData($request);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error AccountReceivableController@paymentDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba mengambil data pembayaran Piutang. Silakan coba lagi.'], 500);
        }
    }

    public function show(Request $request, int $id)
    {
        $data = [
            'currentPage'    => 'finance.account_receivable',
            'breadcrumb'     => [['label' => 'Piutang', 'url' => route('finances.account_receivables.index')], ['label' => $invoice->number ?? 'Detail']],
            'invoice' => $this->accountReceivableService->fetchInvoiceByID($id, $request->reference_type),
            'type' => $request->reference_type,
            'cashBankAccounts' => $this->accountService->fetchAccountData(AccountCategory::CASH_BANK->value),
        ];
        return view('finance.account-receivable.show', $data);
    }

    public function store(AccountReceivableFormRequest $request, int $id)
    {
        try {
            $this->accountReceivableService->storePayment($request, $id);
            return response()->json(['redirect' => route('finances.account_receivables.show', ['id' => $id, 'reference_type' => $request->reference_type]), 'message' => 'Pembayaran berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/AccountReceivableController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menambahkan pembayaran. Silakan coba lagi.'], 500);
        }
    }
}
