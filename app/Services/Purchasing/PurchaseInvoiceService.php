<?php

namespace App\Services\Purchasing;

use App\Services\JournalService;

use App\Enums\AccountSettingEnum;
use App\Enums\PaymentTerm;
use App\Enums\PurchaseInvoiceStatus;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceCost;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\Purchasing\GoodsReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceService
{
    private PurchaseOrderService $purchaseOrderService;
    private GoodsReceiptService $goodsReceiptService;
    private JournalService $journalService;
    public function __construct(PurchaseOrderService $purchaseOrderService, GoodsReceiptService $goodsReceiptService, JournalService $journalService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
        $this->goodsReceiptService = $goodsReceiptService;
        $this->journalService = $journalService;
    }
    public function generatePurchaseInvoiceNumber(): string
    {
        $prefix = 'PI';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = PurchaseInvoice::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);



        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchPurchaseInvoiceTableData(Request $request)
    {
        $query = PurchaseInvoice::with([
            'purchaseOrder:id,number',
            'supplier:id,name,code',
            'warehouse:id,name,code',
        ])
            ->select(
                'id',
                'number',
                'invoice_date',
                'due_date',
                'supplier_id',
                'warehouse_id',
                'purchase_order_id',
                'total_amount',
                'status'
            );

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchaseOrder', function ($q) use ($search) {
                        $q->where('number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $query = $query->orderBy('invoice_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function storePurchaseInvoice(Request $request): PurchaseInvoice
    {
        $purchaseOrder = $this->purchaseOrderService->fetchPurchaseOrderByID($request->purchase_order_id);
        $invoice = PurchaseInvoice::create([
            'company_id' => $purchaseOrder->company_id,
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'warehouse_id' => $purchaseOrder->warehouse_id,
            'number' => $this->generatePurchaseInvoiceNumber(),
            'invoice_date' => now(),
            'payment_terms' => $purchaseOrder->payment_terms,
            'due_date' => now()->addDays(PaymentTerm::day($purchaseOrder->payment_terms)),
            'status' => PurchaseInvoiceStatus::DRAFT->value,
            'subtotal' => 0,
            'discount_percentage' => $purchaseOrder->discount_percentage,
            'discount_amount' => 0,
            'tax_percentage' => $purchaseOrder->tax_percentage,
            'tax_amount' => 0,
            'total_amount' => 0,
            'created_by' => auth()->user()->id,
        ]);

        return $invoice;
    }

    public function updatePurchaseInvoice(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id) {
            $purchaseInvoice = PurchaseInvoice::findOrFail($id);
            $detailsCollection = collect($request->input('details', []));
            $costCollection = collect($request->input('costs', []));
            $subtotal = $detailsCollection->sum(function ($detail) {
                return (($detail['quantity'] ?? 0))
                    *
                    (($detail['unit_price'] ?? 0))
                    *
                    (1 - (($detail['discount_percentage'] ?? 0) / 100));
            });
            $costAmount = $costCollection->sum(function ($cost) {
                return $cost['amount'] ?? 0;
            });
            $discountAmount = $subtotal * ($request->discount_percentage ?? 0) / 100;
            $taxAmount = ($subtotal - $discountAmount) * ($request->tax_percentage ?? 0) / 100;
            $downpaymentAmount = $request->input('down_payment_amount', 0);

            $purchaseInvoice->update([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'discount_percentage' => $request->discount_percentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'down_payment_amount' => $downpaymentAmount,
                'total_amount' => $subtotal - $downpaymentAmount - $discountAmount + $taxAmount + $costAmount,
                'remaining_amount' => $subtotal - $downpaymentAmount - $discountAmount + $taxAmount + $costAmount,
                'note' => $request->note,
                'payment_terms' => $request->payment_terms,
                'status' => $request->status,
            ]);

            // Delete existing items
            PurchaseInvoiceCost::where('purchase_invoice_id', $purchaseInvoice->id)->delete();
            PurchaseInvoiceItem::where('purchase_invoice_id', $purchaseInvoice->id)->delete();

            foreach ($request->input('costs', []) as $cost) {
                PurchaseInvoiceCost::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'amount' => $cost['amount'],
                ]);
            }

            // Create new items
            foreach ($request->details as $detail) {
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'goods_receipt_item_id' => $detail['goods_receipt_item_id'],
                    'purchase_order_item_id' => $detail['purchase_order_item_id'],
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'] ?? 0,
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * (($detail['discount_percentage'] ?? 0) / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] ?? 0) / 100),
                ]);

                if ($request->status === PurchaseInvoiceStatus::OPEN->value) {
                    PurchaseOrderItem::where('id', $detail['purchase_order_item_id'])
                        ->increment('invoiced_quantity', $detail['quantity']);
                }
            }

            if ($request->status === PurchaseInvoiceStatus::OPEN->value) {
                PurchaseOrder::where('id', $purchaseInvoice->purchase_order_id)
                    ->decrement('down_payment_remaining_amount', $downpaymentAmount);
            }

            if ($request->status === PurchaseInvoiceStatus::OPEN->value) {
                $this->postPIJournal($purchaseInvoice);
            }
        });
    }

    public function fetchPurchaseInvoiceByID(int $id): ?PurchaseInvoice
    {
        return PurchaseInvoice::with([
            'purchaseOrder:id,number,down_payment_amount,down_payment_remaining_amount',
            'items:id,purchase_invoice_id,purchase_order_item_id,goods_receipt_item_id,product_id,quantity,unit_price,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'costs:id,purchase_invoice_id,account_id,description,amount',
            'costs.account:id,name,code,category_id',
            'supplier:id,name,code',
            'warehouse:id,name,code',
            'creator:id,username',
            'payments' => fn($q) => $q->orderBy('payment_date', 'desc'),
            'payments.account:id,name,code',
            'payments.creator:id,username',
        ])
            ->select(
                'id',
                'purchase_order_id',
                'company_id',
                'supplier_id',
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

    public function cancelPurchaseInvoice(int $id): void
    {
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);

        if ($purchaseInvoice->status === PurchaseInvoiceStatus::CANCELLED->value) {
            throw ValidationException::withMessages([
                'status' => "Tagihan ini sudah dibatalkan.",
            ]);
        }

        $isDraft = $purchaseInvoice->status === PurchaseInvoiceStatus::DRAFT->value;
        $isOpenWithNoPayment = $purchaseInvoice->status === PurchaseInvoiceStatus::OPEN->value
            && (float) $purchaseInvoice->total_amount === (float) $purchaseInvoice->remaining_amount;

        if (!$isDraft && !$isOpenWithNoPayment) {
            throw ValidationException::withMessages([
                'status' => "Tagihan ini tidak dapat dibatalkan.",
            ]);
        }
        DB::transaction(function () use ($purchaseInvoice) {
            foreach ($purchaseInvoice->items as $item) {
                PurchaseOrderItem::where('id', $item->purchase_order_item_id)
                    ->decrement('invoiced_quantity', $item->quantity);
            }
            $purchaseInvoice->update(['status' => PurchaseInvoiceStatus::CANCELLED->value]);
            PurchaseOrder::where('id', $purchaseInvoice->purchase_order_id)
                ->increment('down_payment_remaining_amount', $purchaseInvoice->down_payment_amount);

            if ($purchaseInvoice->status === PurchaseInvoiceStatus::OPEN->value) {
                $this->reversePIJournal($purchaseInvoice);
            }
        });
    }

    private function postPIJournal(PurchaseInvoice $purchaseInvoice): void
    {
        $payableAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::ACCOUNT_PAYABLE->value)
            ->value('account_id');
        $payableAmount = $purchaseInvoice->total_amount;

        $grniAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::GRNI->value)
            ->value('account_id');
        $grniAmount = $purchaseInvoice->items->sum('total_amount');

        $taxAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::INPUT_TAX->value)
            ->value('account_id');
        $taxAmount = $purchaseInvoice->tax_amount;

        $discountAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::PURCHASE_DISCOUNT->value)
            ->value('account_id');
        $discountAmount = $purchaseInvoice->discount_amount;

        $journalItems = [];
        if ($grniAmount > 0) {
            $journalItems[] = [
                'account_id' => $grniAccountID,
                'debit' => $grniAmount,
            ];
        }

        if ($taxAmount > 0) {
            $journalItems[] = [
                'account_id' => $taxAccountID,
                'debit' => $taxAmount,
            ];
        }

        if ($discountAmount > 0) {
            $journalItems[] = [
                'account_id' => $discountAccountID,
                'credit' => $discountAmount,
            ];
        }

        $journalItems[] = [
            'account_id' => $payableAccountID,
            'credit' => $payableAmount,
        ];

        $this->journalService->post(
            date: null,
            referenceType: PurchaseInvoice::class,
            referenceID: $purchaseInvoice->id,
            description: "Invoice Pembelian #{$purchaseInvoice->number}",
            items: $journalItems
        );

         if ($purchaseInvoice->down_payment_amount <= 0) {
            return;
        }

        $dpAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::PURCHASE_DOWN_PAYMENT->value)
            ->value('account_id');

        $dpJournalItems = [
            [
                'account_id' => $dpAccountID,
                'credit' => $purchaseInvoice->down_payment_amount,
            ],
            [
                'account_id' => $payableAccountID,
                'debit' => $purchaseInvoice->down_payment_amount,
            ],
        ];

        $this->journalService->post(
            date: null,
            referenceType: PurchaseInvoice::class,
            referenceID: $purchaseInvoice->id,
            description: "Alokasi Uang Muka Untuk Invoice Pembelian #{$purchaseInvoice->number}",
            items: $dpJournalItems
        );
    }

    private function reversePIJournal(PurchaseInvoice $purchaseInvoice): void
    {
        $journalEntry = JournalEntry::where('reference_type', PurchaseInvoice::class)
            ->where('reference_id', $purchaseInvoice->id)
            ->get();

        if ($journalEntry->count() > 0) {
            foreach ($journalEntry as $entry) {
                $this->journalService->reverse(
                    journalEntryID: $entry->id,
                    date: null,
                    description: "Pembalikan Jurnal Untuk Invoice Pembelian #{$purchaseInvoice->number}"
                );
            }
        }
    }
}
