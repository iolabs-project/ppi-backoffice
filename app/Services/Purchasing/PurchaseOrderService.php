<?php

namespace App\Services\Purchasing;

use App\Enums\AccountSettingEnum;
use App\Services\NumberingService;
use App\Services\Finance\CashService;
use App\Enums\CashTransactionStatusEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Enums\GoodsReceiptStatus;
use App\Services\JournalService;
use App\Enums\PurchaseInvoiceStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\AccountSetting;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderCost;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Services\DocumentTotals;

class PurchaseOrderService
{
    private JournalService $journalService;
    protected CashService $cashService;
    public function __construct(JournalService $journalService, CashService $cashService)
    {
        $this->journalService = $journalService;
        $this->cashService = $cashService;
    }
    // Purchase Order
    public function generatePONumber(): string
    {
        return app(NumberingService::class)->document('purchase_order', PurchaseOrder::class);
    }

    public function fetchPurchaseOrderTableData(Request $request)
    {
        $query = PurchaseOrder::with([
            'items:id,purchase_order_id,product_id,quantity,received_quantity',
            'warehouse:id,name,code',
            'supplier:id,name,code',
            'goodsReceipts' => function ($query) {
                $query->select('id', 'purchase_order_id', 'status')
                    ->where('status', '<>', GoodsReceiptStatus::CANCELLED->value);
            },
        ])
            ->select(
                'id',
                'number',
                'order_date',
                'supplier_id',
                'warehouse_id',
                'due_date',
                'total_amount',
                'status'
            )
            ->withSum('items as total_quantity', 'quantity')
            ->withSum('items as total_received_quantity', 'received_quantity')
            ->withSum('items as total_invoiced_quantity', 'invoiced_quantity');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('is_receivable') && $request->input('is_receivable')){
            $query->receivable();
        }

        if ($request->filled('is_invoicable') && $request->input('is_invoicable')){
            $query->invoicable();
        }

        if ($request->filled('start_date')) {
            $query->where('order_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('order_date', '<=', $request->input('end_date'));
        }

        $query = $query->orderBy('order_date', 'desc')->orderBy('id', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function fetchPurchaseOrderByID(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::with([
            'items:id,purchase_order_id,product_id,quantity,received_quantity,invoiced_quantity,unit_price,subtotal,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'costs:id,purchase_order_id,account_id,description,amount,billed_by,is_inventory_cost',
            'costs.account:id,category_id,code,name',
            'supplier:id,name,code',
            'warehouse:id,name,code',
            'creator:id,username',
            'goodsReceipts' => function ($query) {
                $query->select('id', 'purchase_order_id', 'status')
                    ->where('status', '<>', GoodsReceiptStatus::CANCELLED->value);
            },
        ])
            ->select(
                'id',
                'company_id',
                'supplier_id',
                'warehouse_id',
                'number',
                'reference_number',
                'order_date',
                'due_date',
                'payment_terms',
                'discount_percentage',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'subtotal',
                'down_payment_amount',
                'down_payment_account_id',
                'total_amount',
                'note',
                'status',
                'created_by',
                'created_at',
                'updated_at',
            )
            ->withSum('items as total_quantity', 'quantity')
            ->withSum('items as total_received_quantity', 'received_quantity')
            ->withSum('items as total_invoiced_quantity', 'invoiced_quantity')
            ->find($id);
    }

    public function storePurchaseOrder(Request $request): void
    {
        DB::transaction(function () use ($request) {
            $detailsCollection = collect($request->input('details', []));
            $costCollection = collect($request->input('costs', []));
            $subtotal = $detailsCollection->sum(function ($item) {
                return DocumentTotals::lineTotal($item['quantity'] ?? 0, $item['unit_price'] ?? 0, $item['discount_percentage'] ?? 0);
            });
            $costTotal = $costCollection->sum(function ($cost) {
                return $cost['amount'] ?? 0;
            });
            $discountAmount = DocumentTotals::percentOf($subtotal, $request->discount_percentage);
            $taxAmount = DocumentTotals::percentOf($subtotal - $discountAmount, $request->tax_percentage);

            $form =  PurchaseOrder::create(
                [
                    'company_id' => config('context.selected_company_id'),
                    'supplier_id' => $request->supplier_id,
                    'warehouse_id' => $request->warehouse_id,
                    'number' => $request->number,
                    'reference_number' => $request->reference_number,
                    'order_date' => $request->order_date,
                    'due_date' => $request->due_date,
                    'discount_percentage' => $request->discount_percentage,
                    'tax_percentage' => $request->tax_percentage,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'down_payment_amount' => $request->down_payment_amount,
                    'down_payment_remaining_amount' => $request->down_payment_amount,
                    'down_payment_account_id' => $request->down_payment_account_id,
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $costTotal,
                    'note' => $request->note,
                    'payment_terms' => $request->payment_terms,
                    'status' => $request->status,
                    'created_by' => Auth::id(),
                ]
            );

            foreach ($request->details as $detail) {
                // Rows the user added but never picked a product for are left out instead of failing the insert
                if (empty($detail['product_id'])) {
                    continue;
                }
                PurchaseOrderItem::create([
                    'purchase_order_id' => $form->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'],
                    'discount_amount' => DocumentTotals::lineDiscount($detail['quantity'], $detail['unit_price'], $detail['discount_percentage'] ?? 0),
                    'total_amount' => DocumentTotals::lineTotal($detail['quantity'], $detail['unit_price'], $detail['discount_percentage'] ?? 0),
                ]);
            }

            foreach ($request->input('costs', []) as $cost) {
                PurchaseOrderCost::create([
                    'purchase_order_id' => $form->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'billed_by' => $cost['billed_by'] ?? 'supplier',
                    'is_inventory_cost' => $cost['is_inventory_cost'] ?? false,
                    'amount' => $cost['amount'],
                ]);
            }

            if ($form->status === PurchaseOrderStatus::OPEN->value) {
                if ($form->down_payment_account_id && $form->down_payment_amount > 0) {
                    $this->postCashTransaction($form);
                    $this->postPOJournal($form);
                }
            }
        });
    }

    public function updatePurchaseOrder(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id) {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            abort_unless_draft($purchaseOrder->status, 'Purchase Order');
            $detailsCollection = collect($request->input('details', []));
            $costCollection = collect($request->input('costs', []));

            $subtotal = $detailsCollection->sum(function ($detail) {
                return DocumentTotals::lineTotal($detail['quantity'] ?? 0, $detail['unit_price'] ?? 0, $detail['discount_percentage'] ?? 0);
            });
            $costTotal = $costCollection->sum(function ($cost) {
                return $cost['amount'] ?? 0;
            });
            $discountAmount = DocumentTotals::percentOf($subtotal, $request->discount_percentage);
            $taxAmount = DocumentTotals::percentOf($subtotal - $discountAmount, $request->tax_percentage);

            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'order_date' => $request->order_date,
                'due_date' => $request->due_date,
                'discount_percentage' => $request->input('discount_percentage', 0),
                'discount_amount' => $discountAmount,
                'tax_percentage' => $request->input('tax_percentage', 0),
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'down_payment_amount' => $request->input('down_payment_amount', 0),
                'down_payment_account_id' => $request->input('down_payment_account_id', null),
                'total_amount' => $subtotal - $request->input('down_payment_amount', 0) - $discountAmount + $taxAmount + $costTotal,
                'note' => $request->input('note', null),
                'payment_terms' => $request->input('payment_terms', null),
                'status' => $request->input('status', null),
            ]);

            // Delete existing items
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->delete();

            // Create new items
            foreach ($request->details as $detail) {
                // Rows the user added but never picked a product for are left out instead of failing the insert
                if (empty($detail['product_id'])) {
                    continue;
                }
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'] ?? 0,
                    'discount_amount' => DocumentTotals::lineDiscount($detail['quantity'], $detail['unit_price'], $detail['discount_percentage'] ?? 0),
                    'total_amount' => DocumentTotals::lineTotal($detail['quantity'], $detail['unit_price'], $detail['discount_percentage'] ?? 0),
                ]);
            }

            // Delete existing costs
            PurchaseOrderCost::where('purchase_order_id', $purchaseOrder->id)->delete();

            foreach ($request->input('costs', []) as $cost) {
                PurchaseOrderCost::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'billed_by' => $cost['billed_by'] ?? 'supplier',
                    'is_inventory_cost' => $cost['is_inventory_cost'] ?? false,
                    'amount' => $cost['amount'],
                ]);
            }
            if ($purchaseOrder->status === PurchaseOrderStatus::OPEN->value) {
                if ($purchaseOrder->down_payment_account_id && $purchaseOrder->down_payment_amount > 0) {
                    $this->postCashTransaction($purchaseOrder);
                    $this->postPOJournal($purchaseOrder);
                }
            }
        });
    }

    public function changePurchaseOrderStatus(int $id, string $status): void
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        abort_unless_can_become($purchaseOrder->status, $status, 'Purchase Order');

        $wasOpen = $purchaseOrder->status === PurchaseOrderStatus::OPEN->value;
        $hasDownPayment = $purchaseOrder->down_payment_amount > 0 && $purchaseOrder->down_payment_account_id;

        // Opening from the list skips the form, so check the draft is complete here
        if ($status === PurchaseOrderStatus::OPEN->value) {
            $errors = [];
            if (!$purchaseOrder->supplier_id) {
                $errors['supplier_id'] = 'Supplier belum dipilih. Lengkapi draft PO terlebih dahulu.';
            }
            if (!$purchaseOrder->warehouse_id) {
                $errors['warehouse_id'] = 'Gudang belum dipilih. Lengkapi draft PO terlebih dahulu.';
            }
            if (!$purchaseOrder->items()->exists()) {
                $errors['details'] = 'PO belum memiliki produk. Lengkapi draft PO terlebih dahulu.';
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
        }

        if ($status === PurchaseOrderStatus::CANCELLED->value && $wasOpen) {
            $hasGoodsReceipts = $purchaseOrder->goodsReceipts()
                ->where('status', '!=', GoodsReceiptStatus::CANCELLED->value)
                ->exists();
            $hasInvoices = PurchaseInvoice::where('purchase_order_id', $purchaseOrder->id)
                ->where('status', '!=', PurchaseInvoiceStatus::CANCELLED->value)
                ->exists();

            if ($hasGoodsReceipts || $hasInvoices) {
                throw ValidationException::withMessages([
                    'status' => 'PO tidak dapat dibatalkan karena sudah memiliki penerimaan barang atau tagihan.',
                ]);
            }
        }

        DB::transaction(function () use ($purchaseOrder, $status, $wasOpen, $hasDownPayment) {
            if ($status === PurchaseOrderStatus::OPEN->value && $hasDownPayment) {
                $this->postCashTransaction($purchaseOrder);
                $this->postPOJournal($purchaseOrder);
            }

            if ($status === PurchaseOrderStatus::CANCELLED->value && $wasOpen && $hasDownPayment) {
                $this->reversePOJournal($purchaseOrder);
                // The down payment cash row never posted its own journal (the PO journal covers it), so just mark it cancelled
                CashTransaction::where('reference_type', PurchaseOrder::class)
                    ->where('reference_id', $purchaseOrder->id)
                    ->update(['status' => CashTransactionStatusEnum::CANCELLED->value]);
            }

            $purchaseOrder->update(['status' => $status]);
        });
    }

    public function fetchPOItemsForGoodsReceipt(int $id): Collection
    {
        $query = PurchaseOrderItem::with(['product:id,code,name,unit_id', 'product.unit:id,name,symbol'])
            ->where('purchase_order_id', $id)
            ->orderBy('id', 'asc');

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_code' => $item->product->code,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'received_quantity' => $item->received_quantity,
                'remaining_quantity' => $item->quantity - $item->received_quantity,
                'unit_price' => $item->unit_price,
                'unit' => $item->product->unit->symbol,
                'discount_percentage' => $item->discount_percentage,
                'discount_amount' => $item->discount_amount,
            ];
        });
    }

    private function postPOJournal(PurchaseOrder $purchaseOrder): void
    {
        $creditAccountID = $purchaseOrder->down_payment_account_id;
        $debitAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
            ->where('setting_key', AccountSettingEnum::PURCHASE_DOWN_PAYMENT->value)
            ->value('account_id');
        $amount = $purchaseOrder->down_payment_amount;

        $journalItems = [
            [
                'account_id' => $debitAccountID,
                'debit' => $amount,
            ],
            [
                'account_id' => $creditAccountID,
                'credit' => $amount,
            ],
        ];

        $this->journalService->post(
            date: null,
            referenceType: PurchaseOrder::class,
            referenceID: $purchaseOrder->id,
            description: 'Uang Muka Pembelian #' . $purchaseOrder->number,
            items: $journalItems
        );
    }

    private function reversePOJournal(PurchaseOrder $purchaseOrder): void
    {
        $journalEntries = JournalEntry::where('reference_type', PurchaseOrder::class)
            ->where('reference_id', $purchaseOrder->id)
            ->get();

        foreach ($journalEntries as $entry) {
            $this->journalService->reverse(
                journalEntryID: $entry->id,
                date: null,
                description: "Reversal of Journal Entry for Purchase Order #{$purchaseOrder->number}"
            );
        }
    }

    public function updateReceivedQuantity(int $purchaseOrderItemID, float $receivedQuantity): void
    {
        $purchaseOrderItem = PurchaseOrderItem::findOrFail($purchaseOrderItemID);
        $purchaseOrderItem->received_quantity += $receivedQuantity;
        $purchaseOrderItem->save();

        PurchaseOrder::where('id', $purchaseOrderItem->purchase_order_id)
            ->whereDoesntHave('items', function ($query) {
                $query->whereColumn('received_quantity', '<', 'quantity');
            })
            ->update(['status' => PurchaseOrderStatus::CLOSED->value]);
    }

    public function updateInvoicedQuantity(int $purchaseOrderItemID, float $invoicedQuantity): void
    {
        $purchaseOrderItem = PurchaseOrderItem::findOrFail($purchaseOrderItemID);
        $purchaseOrderItem->invoiced_quantity += $invoicedQuantity;
        $purchaseOrderItem->save();
    }

    public function decrementDownPaymentRemainingAmount(int $purchaseOrderID, float $amount): void
    {
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderID);
        $purchaseOrder->down_payment_remaining_amount -= $amount;
        if ($purchaseOrder->down_payment_remaining_amount < 0) {
            $purchaseOrder->down_payment_remaining_amount = 0;
        }
        $purchaseOrder->save();
    }

    private function postCashTransaction(PurchaseOrder $po)
    {
        $request = new Request([
            'from_account_id' => $po->down_payment_account_id,
            'contact_id' => $po->supplier_id,
            'transaction_date' => $po->order_date,
            'description' => 'Pembayaran Uang Muka Pembelian #' . $po->number,
            'type' => CashTransactionTypeEnum::SEND->value,
            'status' => CashTransactionStatusEnum::POSTED->value,
            'subtotal' => $po->down_payment_amount,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $po->id,
        ]);

        $this->cashService->storeTransaction($request, $po->company_id);
    }
}
