<?php

namespace App\Services\Sales;

use App\Enums\AccountSettingEnum;
use App\Enums\DeliveryOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\AccountSetting;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Models\SalesOrderCharge;
use App\Models\SalesOrderCost;
use App\Models\SalesOrderItem;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesOrderService
{
    private JournalService $journalService;
    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }
    public function generateSONumber(): string
    {
        $prefix = 'SO';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = SalesOrder::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchSalesOrderTableData(Request $request)
    {
        $query = SalesOrder::with([
            'items:id,sales_order_id,product_id,quantity,shipped_quantity,invoiced_quantity',
            'warehouse:id,name,code',
            'customer:id,name,code',
            'salesPerson:id,name,code',
            'deliveryOrders' => function ($query) {
                $query->select('id', 'sales_order_id', 'status')
                    ->where('status', '<>', DeliveryOrderStatus::CANCELLED->value);
            },
        ])
            ->select(
                'id',
                'number',
                'order_date',
                'customer_id',
                'warehouse_id',
                'sales_person_id',
                'due_date',
                'total_amount',
                'status'
            )
            ->withSum('items as total_quantity', 'quantity')
            ->withSum('items as total_shipped_quantity', 'shipped_quantity')
            ->withSum('items as total_invoiced_quantity', 'invoiced_quantity');

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
                    ->orWhereHas('salesPerson', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $query = $query->orderBy('order_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function fetchSalesOrderByID(int $id): ?SalesOrder
    {
        return SalesOrder::with([
            'items:id,sales_order_id,product_id,quantity,shipped_quantity,invoiced_quantity,unit_price,discount_percentage,discount_amount,total_amount',
            'items.product:id,code,name,unit_id',
            'items.product.unit:id,name,symbol',
            'costs:id,sales_order_id,account_id,description,amount',
            'costs.account:id,code,name,category_id',
            'charges:id,sales_order_id,account_id,description,amount',
            'charges.account:id,code,name,category_id',
            'customer:id,name,code',
            'warehouse:id,name,code',
            'salesPerson:id,name,code',
            'creator:id,username'
        ])
            ->select(
                'id',
                'company_id',
                'customer_id',
                'warehouse_id',
                'sales_person_id',
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
            ->withSum('items as total_shipped_quantity', 'shipped_quantity')
            ->withSum('items as total_invoiced_quantity', 'invoiced_quantity')
            ->find($id);
    }

    public function storeSalesOrder(Request $request): void
    {
        DB::transaction(function () use ($request) {
            $detailsCollection = collect($request->input('details', []));
            $costsCollection = collect($request->input('costs', []));
            $chargesCollection = collect($request->input('charges', []));
            $totalChargeAmount = $chargesCollection->sum('amount');
            $subtotal = $detailsCollection->sum(function ($item) {
                return (($item['quantity'] ?? 0))
                    *
                    (($item['unit_price'] ?? 0))

                    *
                    (1 - (($item['discount_percentage'] ?? 0) / 100));
            });
            $discountAmount = $subtotal * ($request->discount_percentage ?? 0) / 100;
            $taxAmount = ($subtotal - $discountAmount) * ($request->tax_percentage ?? 0) / 100;

            $form =  SalesOrder::create(
                [
                    'company_id' => config('context.selected_company_id'),
                    'customer_id' => $request->customer_id,
                    'warehouse_id' => $request->warehouse_id,
                    'sales_person_id' => $request->sales_person_id,
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
                    'total_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $totalChargeAmount,
                    'note' => $request->note,
                    'payment_terms' => $request->payment_terms,
                    'status' => $request->status,
                    'created_by' => Auth::id(),
                ]
            );

            foreach ($request->details as $detail) {
                SalesOrderItem::create([
                    'sales_order_id' => $form->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'],
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * ($detail['discount_percentage'] / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] / 100)),
                ]);
            }

            foreach ($request->input('costs', []) as $cost) {
                SalesOrderCost::create([
                    'sales_order_id' => $form->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'amount' => $cost['amount'],
                ]);
            }

            foreach ($request->input('charges', []) as $charge) {
                SalesOrderCharge::create([
                    'sales_order_id' => $form->id,
                    'account_id' => $charge['account_id'],
                    'description' => $charge['description'] ?? null,
                    'amount' => $charge['amount'],
                ]);
            }

            if ($form->status === SalesOrderStatus::OPEN->value) {
                $this->postSOJournal($form);
            }
        });
    }

    public function updateSalesOrder(Request $request, int $id): void
    {
        DB::transaction(function () use ($request, $id) {
            $salesOrder = SalesOrder::findOrFail($id);
            $detailsCollection = collect($request->input('details', []));
            $costsCollection = collect($request->input('costs', []));
            $chargesCollection = collect($request->input('charges', []));
            $totalChargeAmount = $chargesCollection->sum('amount');
            $subtotal = $detailsCollection->sum(function ($detail) {
                return (($detail['quantity'] ?? 0))
                    *
                    (($detail['unit_price'] ?? 0))
                    *
                    (1 - (($detail['discount_percentage'] ?? 0) / 100));
            });

            $discountAmount = $subtotal * ($request->discount_percentage ?? 0) / 100;
            $taxAmount = ($subtotal - $discountAmount) * ($request->tax_percentage ?? 0) / 100;

            $salesOrder->update([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'number' => $request->number,
                'reference_number' => $request->reference_number,
                'order_date' => $request->order_date,
                'due_date' => $request->due_date,
                'discount_percentage' => $request->discount_percentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $request->tax_percentage,
                'tax_amount' => $taxAmount,
                'subtotal' => $subtotal,
                'down_payment_amount' => $request->down_payment_amount,
                'down_payment_account_id' => $request->down_payment_account_id,
                'total_amount' => $subtotal - $request->down_payment_amount - $discountAmount + $taxAmount + $totalChargeAmount,
                'note' => $request->note,
                'payment_terms' => $request->payment_terms,
                'status' => $request->status,
            ]);

            // Delete existing items
            SalesOrderCost::where('sales_order_id', $salesOrder->id)->delete();
            SalesOrderCharge::where('sales_order_id', $salesOrder->id)->delete();
            SalesOrderItem::where('sales_order_id', $salesOrder->id)->delete();

            // Create new items
            foreach ($request->details as $detail) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'discount_percentage' => $detail['discount_percentage'] ?? 0,
                    'discount_amount' => $detail['quantity'] * $detail['unit_price'] * (($detail['discount_percentage'] ?? 0) / 100),
                    'total_amount' => $detail['quantity'] * $detail['unit_price'] * (1 - ($detail['discount_percentage'] ?? 0) / 100),
                ]);
            }

            foreach ($request->input('costs', []) as $cost) {
                SalesOrderCost::create([
                    'sales_order_id' => $salesOrder->id,
                    'account_id' => $cost['account_id'],
                    'description' => $cost['description'] ?? null,
                    'amount' => $cost['amount'],
                ]);
            }

            foreach ($request->input('charges', []) as $charge) {
                SalesOrderCharge::create([
                    'sales_order_id' => $salesOrder->id,
                    'account_id' => $charge['account_id'],
                    'description' => $charge['description'] ?? null,
                    'amount' => $charge['amount'],
                ]);
            }

            if ($salesOrder->status === SalesOrderStatus::OPEN->value) {
                $this->postSOJournal($salesOrder);
            }
        });
    }

    public function changeSalesOrderStatus(int $id, string $status): void
    {
        $salesOrder = SalesOrder::findOrFail($id);
        // $salesOrder->update(['status' => $status]);
        DB::transaction(function () use ($salesOrder, $status) {
            if ($status === SalesOrderStatus::CANCELLED->value && $salesOrder->status === SalesOrderStatus::OPEN->value) {
                $this->reverseSOJournal($salesOrder);
            }

            $salesOrder->update(['status' => $status]);
        });
    }

    public function fetchSOItemsForDeliveryOrder(int $id): Collection
    {
        $query = SalesOrderItem::with(['product:id,code,name,unit_id', 'product.unit:id,name,symbol'])
            ->where('sales_order_id', $id)
            ->orderBy('id', 'asc');

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_code' => $item->product->code,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'shipped_quantity' => $item->shipped_quantity,
                'remaining_quantity' => $item->quantity - $item->shipped_quantity,
                'unit_price' => $item->unit_price,
                'unit' => $item->product->unit->symbol,
                'discount_percentage' => $item->discount_percentage,
                'discount_amount' => $item->discount_amount,
            ];
        });
    }

    private function postSOJournal(SalesOrder $salesOrder): void
    {
        if ($salesOrder->down_payment_amount > 0 && $salesOrder->down_payment_account_id) {
            $debitAccountID = $salesOrder->down_payment_account_id;
            $creditAccountID = AccountSetting::where('company_id', config('context.selected_company_id'))
                ->where('setting_key', AccountSettingEnum::SALES_DOWN_PAYMENT->value)
                ->value('account_id');
            $amount = $salesOrder->down_payment_amount;

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
                referenceType: SalesOrder::class,
                referenceID: $salesOrder->id,
                description: 'Uang Muka Penjualan #' . $salesOrder->number,
                items: $journalItems
            );
        }
    }

    private function reverseSOJournal(SalesOrder $salesOrder): void
    {
        $journalEntries = JournalEntry::where('reference_type', SalesOrder::class)
            ->where('reference_id', $salesOrder->id)
            ->get();

        foreach ($journalEntries as $entry) {
            if ($entry->status === 'posted') {
                $this->journalService->reverse(
                    journalEntryID: $entry->id,
                    date: null,
                    description: "Pembalikan Jurnal Untuk Sales Order #{$salesOrder->number}"
                );
            }
        }
    }

    public function updateShippedQuantity(int $salesOrderItemID, float $shippedQuantity): void
    {
        $salesOrderItem = SalesOrderItem::findOrFail($salesOrderItemID);
        $salesOrderItem->shipped_quantity += $shippedQuantity;
        $salesOrderItem->save();

        SalesOrder::where('id', $salesOrderItem->sales_order_id)
            ->whereDoesntHave('items', function ($query) {
                $query->whereColumn('shipped_quantity', '<', 'quantity');
            })
            ->update(['status' => SalesOrderStatus::CLOSED->value]);
    }

    public function updateInvoicedQuantity(int $salesOrderItemID, float $invoicedQuantity): void
    {
        $salesOrderItem = SalesOrderItem::findOrFail($salesOrderItemID);
        $salesOrderItem->invoiced_quantity += $invoicedQuantity;
        $salesOrderItem->save();
    }

    public function decrementDownPaymentRemainingAmount(int $salesOrderID, float $amount): void
    {
        $salesOrder = SalesOrder::findOrFail($salesOrderID);
        $salesOrder->down_payment_remaining_amount -= $amount;
        if ($salesOrder->down_payment_remaining_amount < 0) {
            $salesOrder->down_payment_remaining_amount = 0;
        }
        $salesOrder->save();
    }
}
