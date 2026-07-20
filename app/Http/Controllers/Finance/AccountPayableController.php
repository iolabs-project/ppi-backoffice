<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Finance\AccountPayableService;
use App\Services\Purchasing\PurchaseInvoiceService;

class AccountPayableController extends Controller
{
    private AccountPayableService $accountPayableService;
    private PurchaseInvoiceService $purchaseInvoiceService;

    public function __construct(AccountPayableService $accountPayableService, PurchaseInvoiceService $purchaseInvoiceService)
    {
        $this->accountPayableService = $accountPayableService;
        $this->purchaseInvoiceService = $purchaseInvoiceService;
    }

   public function index()
    {
        $data = [
            'currentPage'    => 'finance',
            'breadcrumb'     => [['label' => 'Hutang']],
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
            'currentPage'    => 'finance',
            'breadcrumb'     => [['label' => 'Hutang', 'url' => route('account_payables.index')], ['label' => 'Detail']],
            'purchaseInvoice' => $purchaseInvoice,
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
            return response()->json(['redirect' => route('account_payables.show', $id), 'message' => 'Pembayaran berhasil ditambahkan.']);
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
