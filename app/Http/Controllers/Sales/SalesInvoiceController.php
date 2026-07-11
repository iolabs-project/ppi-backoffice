<?php

namespace App\Http\Controllers\Sales;

use App\Enums\PaymentTerm;
use App\Enums\SalesInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Services\Sales\DeliveryOrderService;
use App\Services\Sales\SalesInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SalesInvoiceController extends Controller
{
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

    public function store(Request $request, SalesInvoiceService $salesInvoiceService)
    {
        try {
            $request->validate(
                [
                    'sales_order_id' => 'required|exists:sales_orders,id',
                ],
                [
                    'sales_order_id.required' => 'ID Sales Order harus diisi.',
                    'sales_order_id.exists' => 'Sales Order tidak ditemukan.',
                ]
            );

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

        $data = [
            'currentPage' => 'penjualan.tagihan',
            'breadcrumb'  => [
                ['label' => 'Tagihan', 'url' => route('sales.sales_invoices.index')],
                ['label' => 'Edit'],
            ],
            'salesInvoice' => $salesInvoice,
            'paymentTerms' => PaymentTerm::dropdownOptions(),
            'remainingDOItems' => $deliveryOrderService->fetchDOItemsForSalesInvoice($salesInvoice->sales_order_id),
        ];
        return view('sales.sales-invoice.edit', $data);
    }

    public function update(Request $request, SalesInvoiceService $salesInvoiceService, int $id)
    {
        if ($request->input('status') !== SalesInvoiceStatus::DRAFT->value) {
            $request->validate(
                [
                    'reference_number' => 'nullable|string|max:50',
                    'invoice_date' => 'required|date',
                    'due_date' => 'required|date|after_or_equal:invoice_date',
                    'status' => 'required|in:open',
                    'payment_terms' => 'required|in:net_7,net_14,net_30,net_45',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'down_payment_amount' => 'nullable|numeric|min:0',
                    'subtotal' => 'nullable|numeric|min:0',
                    'total_amount' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string|max:1000',
                    'details' => 'required|array|min:1',
                    'details.*.delivery_order_item_id' => 'required|exists:delivery_order_items,id',
                    'details.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
                    'details.*.product_id' => 'required|exists:products,id',
                    'details.*.quantity' => 'required|numeric|min:0',
                    'details.*.unit_price' => 'required|numeric|min:0',
                    'details.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
                    'charges' => 'nullable|array',
                    'charges.*.account_id' => 'required|exists:chart_of_accounts,id',
                    'charges.*.description' => 'nullable|string|max:1000',
                    'charges.*.amount' => 'required|numeric|min:0',
                ],
                [
                    'invoice_date.required' => 'Tanggal invoice harus diisi.',
                    'due_date.required' => 'Tanggal jatuh tempo harus diisi.',
                    'due_date.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice.',
                    'status.required' => 'Status invoice harus diisi.',
                    'details.required' => 'Daftar item invoice harus diisi.',
                    'details.*.delivery_order_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item DO.',
                    'details.*.sales_order_item_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item SO.',
                    'details.*.product_id.required' => 'Terdapat produk yang belum dipilih. Silakan pilih produk dari daftar item SO.',
                    'details.*.quantity.required' => 'Terdapat produk yang belum diisi jumlah. Silakan isi jumlah yang diharapkan untuk setiap produk.',
                    'details.*.unit_price.required' => 'Terdapat produk yang belum diisi harga satuannya. Silakan isi harga satuan untuk setiap produk.',
                    'details.*.discount_percentage.required' => 'Terdapat produk yang belum diisi persentase diskon. Silakan isi persentase diskon untuk setiap produk.',
                    'charges.*.account_id.required' => 'Terdapat biaya yang belum dipilih. Silakan pilih akun untuk setiap biaya.',
                    'charges.*.amount.required' => 'Terdapat biaya yang belum diisi jumlahnya. Silakan isi jumlah untuk setiap biaya.',
                ]
            );
        }

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
