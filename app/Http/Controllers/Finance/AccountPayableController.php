<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Enums\AccountCategoryEnum;
use App\Enums\AccountPayableStatusEnum;
use App\Enums\PayablePaymentReferenceTypeEnum;
use App\Http\Requests\Finance\AccountPayableFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\Finance\AccountPayableService;
use App\Services\Purchasing\PurchaseInvoiceService;
use App\Services\ExpenseService;
use App\Services\Master\AccountService;

class AccountPayableController extends Controller
{
    private AccountPayableService $accountPayableService;
    private AccountService $accountService;

    public function __construct(AccountPayableService $accountPayableService, AccountService $accountService)
    {
        $this->accountPayableService = $accountPayableService;
        $this->accountService = $accountService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'finance.account_payable',
            'breadcrumb'     => [['label' => 'Hutang']],
            'status' => AccountPayableStatusEnum::dropdownOptions(),
        ];
        return view('finance.account-payable.index', $data);
    }

    public function datatable(Request $request)
    {
        try {
            $data = $this->accountPayableService->fetchAPTableData($request);
            return response()->json($data);
         } catch (\Exception $e) {
            Log::error('Error AccountPayableController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba mengambil data Hutang. Silakan coba lagi.'], 500);
        }
    }

    public function paymentDatatable(Request $request)
    {
        try {
            $data = $this->accountPayableService->fetchPaymentTableData($request);
            return response()->json($data);
         } catch (\Exception $e) {
            Log::error('Error AccountPayableController@paymentDatatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba mengambil data pembayaran Hutang. Silakan coba lagi.'], 500);
        }
    }

    public function show(Request $request, int $id)
    {
        $companyID = config('context.selected_company_id');
        $data = [
            'currentPage'    => 'finance.account_payable',
            'breadcrumb'     => [['label' => 'Hutang', 'url' => route('finances.account_payables.index')], ['label' => $invoice->number ?? 'Detail']],
            'invoice' => $this->accountPayableService->fetchInvoiceByID($id, $request->reference_type),
            'type' => $request->reference_type,
            'cashBankAccounts' => $this->accountService->fetchAccountData(companyID: $companyID, categoryID:AccountCategoryEnum::CASH_BANK->value),
        ];
        return view('finance.account-payable.show', $data);
    }

    public function store(AccountPayableFormRequest $request, int $id)
    {
        try {
            $this->accountPayableService->storePayment($request, $id);
            return response()->json(['redirect' => route('finances.account_payables.show', ['id' => $id, 'reference_type' => $request->reference_type]), 'message' => 'Pembayaran berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error Finance/AccountPayableController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menambahkan pembayaran. Silakan coba lagi.'], 500);
        }
    }
}
