<?php

namespace App\Services\Sales;

use App\Enums\PaymentTerm;
use App\Services\JournalService;
use App\Enums\AccountSettingEnum;
use App\Enums\SalesInvoiceStatus;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceCharge;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class SalesInvoiceService
{

    private SalesOrderService $salesOrderService;
    private JournalService $journalService;

    public function __construct(SalesOrderService $salesOrderService, JournalService $journalService)
    {
        $this->salesOrderService = $salesOrderService;
        $this->journalService = $journalService;
    }

    public function generateSINumber(): string
    {
        $prefix = 'SI';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = SalesInvoice::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchSalesInvoiceTableData(Request $request)
    {
        $query = SalesInvoice::with([
            'salesOrder:id,number',
            'customer:id,name,code',
            'warehouse:id,name,code',
        ])
            ->select(
                'id',
                'number',
                'invoice_date',
                'due_date',
                'customer_id',
                'warehouse_id',
                'sales_order_id',
                'total_amount',
                'status'
            );

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('salesOrder', function ($q) use ($search) {
                        $q->where('number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date')) {
            $query->where('invoice_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('invoice_date', '<=', $request->input('end_date'));
        }

        $query = $query->orderBy('invoice_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function storeSalesInvoice(Request $request): SalesInvoice
    {
        $salesOrder =  $this->salesOrderService->fetchSalesOrderByID($request->sales_order_id);
        return DB::transaction(function () use ($salesOrder) {
            $invoice =  SalesInvoice::create([
                'company_id' => $salesOrder->company_id,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'sales_person_id' => $salesOrder->sales_person_id,
                'warehouse_id' => $salesOrder->warehouse_id,
                'number' => $this->generateSINumber(),
                'invoice_date' => now(),
                'payment_terms' => $salesOrder->payment_terms,
                'due_date' => now()->addDays(PaymentTerm::day($salesOrder->payment_terms)),
                'status' => SalesInvoiceStatus::DRAFT->value,
                'subtotal' => 0,
                'discount_percentage' => $salesOrder->discount_percentage,
                'discount_amount' => 0,
                'tax_percentage' => $salesOrder->tax_percentage,
                'tax_amount' => 0,
                'total_amount' => 0,
                'created_by' => Auth::id(),
            ]);

            foreach ($salesOrder->charges as $charge) {
                SalesInvoiceCharge::create([
                    'sales_invoice_id' => $invoice->id,
                    'account_id' => $charge->account_id,
                    'description' => $charge->description,
                    'amount' => $charge->amount,
                ]);
            }

            return $invoice;
        });
    }

    public function updateSalesInvoice(Request $request, int $id)
    {
        $salesInvoice = SalesInvoice::findOrFail($id);
        $detailsCollection = collect($request->input('details', []));
        $chargesCollection = collect($request->input('charges', []));
        $subtotal = $detailsCollection->sum(function ($detail) {
            return (($detail['quantity'] ?? 0))
                *
                (($detail['unit_price'] ?? 0))
                *
                (1 - (($detail['discount_percentage'] ?? 0) / 100));
        });
        $chargesTotal = $chargesCollection->sum('amount');
        $discountAmount = $subtotal * ($request->discount_percentage ?? 0) / 100;
        $taxAmount = ($subtotal - $discountAmount) * ($request->tax_percentage ?? 0) / 100;

        DB::transaction(function () use ($salesInvoice, $request, $subtotal, $discountAmount, $taxAmount, $chargesTotal) {
            $salesInvoice->update([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'sales_person_id' => $request->sales_person_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'discount_percentage' => $request->discount_percentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'down_payment_amount' => $request->down_payment_amount,
                'total_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $chargesTotal,
                'remaining_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $chargesTotal,
                'note' => $request->note,
                'payment_terms' => $request->payment_terms,
                'status' => $request->status,
            ]);

            SalesInvoiceItem::where('sales_invoice_id', $salesInvoice->id)->delete();
            SalesInvoiceCharge::where('sales_invoice_id', $salesInvoice->id)->delete();

            foreach ($request->details as $detail) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'delivery_order_item_id' => $detail['delivery_order_item_id'],
                    'sales_order_item_id' => $detail['sales_order_item_id'],
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'] ?? 0,
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * (($detail['discount_percentage'] ?? 0) / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] ?? 0) / 100),
                ]);

                if ($request->status === SalesInvoiceStatus::OPEN->value) {
                    $this->salesOrderService->updateInvoicedQuantity(
                        salesOrderItemID: $detail['sales_order_item_id'],
                        invoicedQuantity: $detail['quantity']
                    );
                }
            }

            foreach ($request->input('charges', []) as $charge) {
                SalesInvoiceCharge::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'account_id' => $charge['account_id'],
                    'description' => $charge['description'] ?? null,
                    'amount' => $charge['amount'],
                ]);
            }

            if ($request->status === SalesInvoiceStatus::OPEN->value) {
                $this->salesOrderService->decrementDownPaymentRemainingAmount(
                    salesOrderID: $salesInvoice->sales_order_id,
                    amount: $request->down_payment_amount
                );
                $this->postSIJournal($salesInvoice->fresh()->load('charges'));
            }
        });
    }

    public function fetchSalesInvoiceByID(int $id): ?SalesInvoice
    {
        return SalesInvoice::with([
            'salesOrder:id,number,down_payment_amount,down_payment_remaining_amount',
            'items:id,sales_invoice_id,sales_order_item_id,delivery_order_item_id,product_id,quantity,unit_price,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'charges:id,sales_invoice_id,account_id,description,amount',
            'charges.account:id,code,name,category_id',
            'customer:id,name,code',
            'salesPerson:id,name,code',
            'warehouse:id,name,code',
            'creator:id,username',
            'payments' => fn($q) => $q->orderBy('payment_date', 'desc'),
            'payments.account:id,name,code',
            'payments.creator:id,username',
        ])
            ->select(
                'id',
                'sales_order_id',
                'company_id',
                'customer_id',
                'sales_person_id',
                'warehouse_id',
                'number',
                'reference_number',
                'invoice_date',
                'due_date',
                'payment_terms',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'down_payment_amount',
                'subtotal',
                'total_amount',
                'remaining_amount',
                'note',
                'status',
                'created_by',
                'created_at',
                'updated_at',
            )
            ->find($id);
    }

    public function cancelSalesInvoice(int $id): void
    {
        $salesInvoice = SalesInvoice::findOrFail($id);

        if ($salesInvoice->status === SalesInvoiceStatus::CANCELLED->value) {
            throw ValidationException::withMessages([
                'status' => "Tagihan ini sudah dibatalkan.",
            ]);
        }

        $isDraft = $salesInvoice->status === SalesInvoiceStatus::DRAFT->value;
        $isOpenWithNoPayment = $salesInvoice->status === SalesInvoiceStatus::OPEN->value
            && (float) $salesInvoice->total_amount === (float) $salesInvoice->remaining_amount;

        if (!$isDraft && !$isOpenWithNoPayment) {
            throw ValidationException::withMessages([
                'status' => "Tagihan ini tidak dapat dibatalkan.",
            ]);
        }
        DB::transaction(function () use ($salesInvoice) {
            foreach ($salesInvoice->items as $item) {
                SalesOrderItem::where('id', $item->sales_order_item_id)
                    ->decrement('invoiced_quantity', $item->quantity);
            }
            if ($salesInvoice->status === SalesInvoiceStatus::OPEN->value) {
                $this->reverseSIJournal($salesInvoice);
            }
            $salesInvoice->update(['status' => SalesInvoiceStatus::CANCELLED->value]);
            SalesOrder::where('id', $salesInvoice->sales_order_id)
                ->increment('down_payment_remaining_amount', $salesInvoice->down_payment_amount);
        });
    }

    private function postSIJournal(SalesInvoice $salesInvoice): void
    {
        $salesInvoice->load('items', 'charges');
        $receivableAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::ACCOUNT_RECEIVABLE->value)
            ->value('account_id');
        $receivableAmount = $salesInvoice->items->sum('total_amount') + $salesInvoice->charges->sum('amount') + $salesInvoice->tax_amount - $salesInvoice->discount_amount;

        $revenueID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::SALES_REVENUE->value)
            ->value('account_id');
        $revenueAmount = $salesInvoice->items->sum('total_amount');

        $discountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::SALES_DISCOUNT->value)
            ->value('account_id');
        $discountAmount = $salesInvoice->discount_amount;

        $taxID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::OUTPUT_TAX->value)
            ->value('account_id');
        $taxAmount = $salesInvoice->tax_amount;

        $journalItems = [];
        if ($receivableAmount > 0) {
            $journalItems[] = [
                'account_id' => $receivableAccountID,
                'debit' => $receivableAmount,
            ];
        }

        if ($discountAmount > 0) {
            $journalItems[] = [
                'account_id' => $discountID,
                'debit' => $discountAmount,
            ];
        }

        if ($revenueAmount > 0) {
            $journalItems[] = [
                'account_id' => $revenueID,
                'credit' => $revenueAmount,
            ];
        }

        if ($taxAmount > 0) {
            $journalItems[] = [
                'account_id' => $taxID,
                'credit' => $taxAmount,
            ];
        }

        foreach ($salesInvoice->charges as $charge) {
            $journalItems[] = [
                'account_id' => $charge->account_id,
                'credit' => $charge->amount,
            ];
        }

        $this->journalService->post(
            date: null,
            referenceType: SalesInvoice::class,
            referenceID: $salesInvoice->id,
            description: 'Invoice Penjualan #' . $salesInvoice->number,
            items: $journalItems
        );

        if ($salesInvoice->down_payment_amount <= 0) {
            return;
        }

        $downPaymentAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::SALES_DOWN_PAYMENT->value)
            ->value('account_id');
        $downPaymentAmount = $salesInvoice->down_payment_amount;

        $this->journalService->post(
            date: null,
            referenceType: SalesInvoice::class,
            referenceID: $salesInvoice->id,
            description: 'Alokasi Uang Muka Penjualan #' . $salesInvoice->number,
            items: [
                [
                    'account_id' => $downPaymentAccountID,
                    'debit' => $downPaymentAmount,
                ],
                [
                    'account_id' => $receivableAccountID,
                    'credit' => $downPaymentAmount,
                ],
            ]
        );
    }

    private function reverseSIJournal(SalesInvoice $salesInvoice): void
    {
        $journalEntries = JournalEntry::where('reference_type', SalesInvoice::class)
            ->where('reference_id', $salesInvoice->id)
            ->get();

        foreach ($journalEntries as $entry) {
            $this->journalService->reverse(
                journalEntryID: $entry->id,
                date: null,
                description: 'Pembalikan Jurnal Untuk Invoice Penjualan #' . $salesInvoice->number
            );
        }
    }
}
