<?php

namespace App\Http\Controllers\Sales;

use App\Http\Requests\Sales\SalesInvoiceFormRequest;
use App\Enums\PaymentTerm;
use App\Enums\SalesInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Services\Master\AccountService;
use App\Services\Sales\DeliveryOrderService;
use App\Services\Sales\SalesInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SalesInvoiceController extends Controller
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index()
    {
        $data = [
            'currentPage'    => 'penjualan.tagihan',
            'breadcrumb'     => [['label' => 'Tagihan']],
            'status' => SalesInvoiceStatus::dropdownOptions(),
        ];
        return view('sales.sales-invoice.index', $data);
    }

    public function datatable(Request $request, SalesInvoiceService $salesInvoiceService)
    {
        $data = $salesInvoiceService->fetchSalesInvoiceTableData($request);
        return response()->json($data);
    }

    public function store(SalesInvoiceFormRequest $request, SalesInvoiceService $salesInvoiceService)
    {
        try {
            $data = $salesInvoiceService->storeSalesInvoice($request);

            return response()->json(['redirect' => route('sales.sales_invoices.edit', $data->id), 'message' => 'Sales invoice berhasil dibuat.']);
        } catch (ValidationException $e) {
            Log::error('Error SalesInvoiceController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error SalesInvoiceController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membuat tagihan penjualan. Silakan coba lagi.'], 500);
        }
    }

    public function edit(SalesInvoiceService $salesInvoiceService, DeliveryOrderService $deliveryOrderService, int $id)
    {
        $salesInvoice = $salesInvoiceService->fetchSalesInvoiceByID($id);
        if (!$salesInvoice) {
            abort(404, 'Tagihan penjualan tidak ditemukan.');
        }
        $companyID = config('context.selected_company_id');

        $data = [
            'currentPage' => 'penjualan.tagihan',
            'breadcrumb'  => [
                ['label' => 'Tagihan', 'url' => route('sales.sales_invoices.index')],
                ['label' => 'Edit'],
            ],
            'salesInvoice' => $salesInvoice,
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'remainingDOItems' => $deliveryOrderService->fetchDOItemsForSalesInvoice($salesInvoice->sales_order_id),
            'accounts' => $this->accountService->fetchAccountData(companyID: $companyID),
        ];
        return view('sales.sales-invoice.edit', $data);
    }

    public function update(SalesInvoiceFormRequest $request, SalesInvoiceService $salesInvoiceService, int $id)
    {
        try {
            $salesInvoiceService->updateSalesInvoice($request, $id);
            return response()->json(['redirect' => route('sales.sales_invoices.index'), 'message' => 'Invoice Penjualan berhasil diperbarui.']);
        } catch (ValidationException $e) {
            Log::error('Error SalesInvoiceController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error SalesInvoiceController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba memperbarui Invoice Penjualan. Silakan coba lagi.'], 500);
        }
    }

    public function cancel(SalesInvoiceService $salesInvoiceService, int $id)
    {
        try {
            $salesInvoiceService->cancelSalesInvoice($id);
            return response()->json(['message' => 'Invoice Penjualan berhasil dibatalkan.']);
        } catch (\Exception $e) {
            Log::error('Error SalesInvoiceController@cancel: ' . $e->getMessage(), [
                'exception' => $e,
                'sales_invoice_id' => $id,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba membatalkan Invoice Penjualan. Silakan coba lagi.'], 500);
        }
    }
}
