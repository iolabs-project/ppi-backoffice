<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Enums\AccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\Finance\AccountPayableService;
use App\Services\Purchasing\PurchaseInvoiceService;
use App\Services\Master\AccountService;

class AccountPayableController extends Controller
{
    private AccountPayableService $accountPayableService;
    private PurchaseInvoiceService $purchaseInvoiceService;
    private AccountService $accountService;

    public function __construct(AccountPayableService $accountPayableService, PurchaseInvoiceService $purchaseInvoiceService, AccountService $accountService)
    {
        $this->accountPayableService = $accountPayableService;
        $this->purchaseInvoiceService = $purchaseInvoiceService;
        $this->accountService = $accountService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'finance.account_payable',
            'breadcrumb'     => [['label' => 'Hutang']],
            'status' => [
                ['id' => 'all', 'name' => 'Semua'],
                ['id' => 'not-yet-due', 'name' => 'Not Yet Due'],
                ['id' => 'unpaid', 'name' => 'Unpaid'],
                ['id' => 'partial', 'name' => 'Partial'],
                ['id' => 'paid', 'name' => 'Paid'],
            ],
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

    public function show(int $id)
    {
        $purchaseInvoice = $this->purchaseInvoiceService->fetchPurchaseInvoiceByID($id);
        $data = [
            'currentPage'    => 'finance.account_payable',
            'breadcrumb'     => [['label' => 'Hutang', 'url' => route('finances.account_payables.index')], ['label' => $purchaseInvoice->number ?? 'Detail']],
            'purchaseInvoice' => $purchaseInvoice,
            'displayStatus' => $purchaseInvoice ? $this->accountPayableService->derivePaymentStatus($purchaseInvoice->status, $purchaseInvoice->due_date) : null,
            'cashBankAccounts' => $this->accountService->fetchAccountData(AccountCategory::CASH_BANK->value),
        ];
        return view('finance.account-payable.show', $data);
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
            $this->accountPayableService->storePayment($request, $id);
            return response()->json(['redirect' => route('finances.account_payables.show', $id), 'message' => 'Pembayaran berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first() ?? 'Data tidak valid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error AccountPayableController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba menambahkan pembayaran. Silakan coba lagi.'], 500);
        }
    }
}
