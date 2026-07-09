<?php

namespace App\Services\Sales;

use App\Enums\DeliveryOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Company;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\DeliveryOrderItemBatch;
use App\Models\InventoryTransaction;
use App\Models\ProductBatch;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryOrderService
{
    private SalesOrderService $salesOrderService;
    public function __construct(SalesOrderService $salesOrderService)
    {
        $this->salesOrderService = $salesOrderService;
    }
    public function generateDONumber(): string
    {
        $prefix = 'DO';
        $companyCode = Company::select('code')->where('id', config('context.selected_company_id'))->first()->code ?? 'XXX';
        $datePart = date('Y');

        $counter = DeliveryOrder::whereYear('created_at', date('Y'))
            ->where('company_id', config('context.selected_company_id'))
            ->count() + 1;
        $counter = str_pad($counter, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$companyCode}-{$datePart}-{$counter}";
    }

    public function fetchDeliveryOrderTableData(Request $request)
    {
        $query = DeliveryOrder::with([
            'salesOrder:id,number',
            'warehouse:id,name,code',
            'customer:id,name,code',
            'items:id,delivery_order_id,sales_order_item_id,product_id,quantity',
            'items.product:id,name,code',
            'items.batches:id,delivery_order_item_id,product_batch_id,quantity,unit_cost',
            'items.batches.productBatch:id,batch_number',
        ])

            ->select(
                'id',
                'number',
                'delivery_date',
                'customer_id',
                'warehouse_id',
                'sales_order_id',
                'status'
            )->withSum('items as total_shipped_quantity', 'quantity');

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

        $query = $query->orderBy('delivery_date', 'desc')->paginate($request->input('per_page', 10));
        return $query;
    }

    public function storeDeliveryOrder(Request $request): DeliveryOrder
    {
        $salesOrder =  $this->salesOrderService->fetchSalesOrderByID($request->sales_order_id);
        return DeliveryOrder::create([
            'company_id' => $salesOrder->company_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'warehouse_id' => $salesOrder->warehouse_id,
            'number' => $this->generateDONumber(),
            'delivery_date' => now(),
            'status' => DeliveryOrderStatus::DRAFT->value,
            'created_by' => auth()->user()->id,
        ]);
    }

    public function fetchDeliveryOrderByID(int $id): ?DeliveryOrder
    {
        return DeliveryOrder::with([
            'salesOrder:id,number',
            'warehouse:id,name,code',
            'customer:id,name,code',
            'items:id,delivery_order_id,sales_order_item_id,product_id,quantity',
            'items.product:id,name,code,unit_id',
            'items.product.unit:id,symbol',
            'items.batches:id,delivery_order_item_id,product_batch_id,quantity,unit_cost',
            'items.batches.productBatch:id,batch_number',
        ])
            ->select(
                'id',
                'company_id',
                'number',
                'reference_number',
                'delivery_date',
                'note',
                'customer_id',
                'warehouse_id',
                'sales_order_id',
                'status'
            )
            ->find($id);
    }

    public function fetchAvailableBatches(int $warehouseId, array $productIds): Collection
    {
        if (empty($productIds)) {
            return collect();
        }

        return ProductBatch::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->whereColumn('quantity', '>', 'reserved_quantity')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'batch_number' => $batch->batch_number,
                    'available_quantity' => $batch->available_quantity,
                    'unit_cost' => $batch->unit_cost,
                ];
            })
            ->values();
    }

    public function updateDeliveryOrder(Request $request, int $id): void
    {
        $header = DeliveryOrder::findOrFail($id);
        $requestCollection = collect($request->except('details'));
        $detailsCollection = collect($request->input('details', []));

        DB::transaction(function () use ($requestCollection, $detailsCollection, $header) {
            $header->update([
                'reference_number' => $requestCollection->get('reference_number'),
                'delivery_date' => $requestCollection->get('delivery_date'),
                'status' => $requestCollection->get('status'),
                'note' => $requestCollection->get('note'),
            ]);

            DeliveryOrderItem::where('delivery_order_id', $header->id)->delete();

            if ($requestCollection->get('status') === DeliveryOrderStatus::DRAFT->value) {
                $this->saveDraftDeliveryOrder($header, $detailsCollection);
            } elseif ($requestCollection->get('status') === DeliveryOrderStatus::FINISHED->value) {
                $this->finalizeDeliveryOrder($header, $detailsCollection);
            }
        });
    }

    private function saveDraftDeliveryOrder(DeliveryOrder $deliveryOrder, Collection $detailsCollection): void
    {
        foreach ($detailsCollection as $detail) {
            $item = DeliveryOrderItem::create([
                'delivery_order_id' => $deliveryOrder->id,
                'sales_order_item_id' => $detail['sales_order_item_id'],
                'product_id' => $detail['product_id'],
                'quantity' => (float) ($detail['quantity'] ?? 0),
            ]);

            foreach (($detail['batches'] ?? []) as $batch) {
                $batchQty = (float) ($batch['quantity'] ?? 0);
                if ($batchQty <= 0) {
                    continue;
                }

                DeliveryOrderItemBatch::create([
                    'delivery_order_item_id' => $item->id,
                    'product_batch_id' => $batch['product_batch_id'],
                    'quantity' => $batchQty,
                    'unit_cost' => $batch['unit_cost'] ?? 0,
                ]);
            }
        }
    }

    private function finalizeDeliveryOrder(DeliveryOrder $deliveryOrder, Collection $detailsCollection): void
    {
        foreach ($detailsCollection as $detail) {
            $quantity = (float) ($detail['quantity'] ?? 0);
            $batches = collect($detail['batches'] ?? []);
            $batchTotal = $batches->sum(fn($b) => (float) ($b['quantity'] ?? 0));

            if (abs($batchTotal - $quantity) > 0.0001) {
                throw ValidationException::withMessages([
                    'details' => "Total quantity batch untuk produk pada baris ini ({$batchTotal}) harus sama dengan Quantity Perlu Dikirim ({$quantity}).",
                ]);
            }

            $salesOrderItem = SalesOrderItem::where('id', $detail['sales_order_item_id'])
                ->lockForUpdate()
                ->first();

            if (!$salesOrderItem) {
                throw ValidationException::withMessages([
                    'details' => 'Item Sales Order tidak ditemukan.',
                ]);
            }

            $remainingOnSO = $salesOrderItem->quantity - $salesOrderItem->shipped_quantity;
            if ($quantity > $remainingOnSO + 0.0001) {
                throw ValidationException::withMessages([
                    'details' => "Quantity pengiriman ({$quantity}) melebihi sisa yang belum dikirim pada Sales Order ({$remainingOnSO}). Sudah dikirim oleh Delivery Order lain.",
                ]);
            }

            $item = DeliveryOrderItem::create([
                'delivery_order_id' => $deliveryOrder->id,
                'sales_order_item_id' => $detail['sales_order_item_id'],
                'product_id' => $detail['product_id'],
                'quantity' => $quantity,
            ]);

            foreach ($batches as $batchDetail) {
                $batchQty = (float) ($batchDetail['quantity'] ?? 0);
                if ($batchQty <= 0) {
                    continue;
                }

                $productBatch = ProductBatch::where('id', $batchDetail['product_batch_id'])
                    ->where('warehouse_id', $deliveryOrder->warehouse_id)
                    ->where('product_id', $detail['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$productBatch) {
                    $batchNumber = $batchDetail['batch_number'] ?? '';
                    throw ValidationException::withMessages([
                        'details' => "Batch '{$batchNumber}' tidak ditemukan atau bukan milik produk/gudang ini.",
                    ]);
                }

                if ($productBatch->available_quantity < $batchQty) {
                    throw ValidationException::withMessages([
                        'details' => "Stok batch '{$productBatch->batch_number}' tidak mencukupi untuk mengirim {$batchQty} unit.",
                    ]);
                }

                DeliveryOrderItemBatch::create([
                    'delivery_order_item_id' => $item->id,
                    'product_batch_id' => $productBatch->id,
                    'quantity' => $batchQty,
                    'unit_cost' => $productBatch->unit_cost,
                ]);

                $productBatch->decrement('quantity', $batchQty);

                $latestTransaction = InventoryTransaction::where('company_id', $deliveryOrder->company_id)
                    ->where('product_id', $detail['product_id'])
                    ->where('warehouse_id', $deliveryOrder->warehouse_id)
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->first();

                $stockBefore = $latestTransaction ? $latestTransaction->stock_after : 0;

                InventoryTransaction::create([
                    'company_id' => $deliveryOrder->company_id,
                    'warehouse_id' => $deliveryOrder->warehouse_id,
                    'product_id' => $detail['product_id'],
                    'product_batch_id' => $productBatch->id,
                    'type' => 'sale',
                    'direction' => -1,
                    'quantity' => $batchQty,
                    'unit_cost' => $productBatch->unit_cost,
                    'total_cost' => $batchQty * $productBatch->unit_cost,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore - $batchQty,
                    'reference_type' => DeliveryOrder::class,
                    'reference_id' => $deliveryOrder->id,
                    'transaction_date' => now(),
                    'note' => 'Pengiriman Barang dari DO #' . $deliveryOrder->number,
                ]);
            }

            SalesOrderItem::where('id', $detail['sales_order_item_id'])
                ->increment('shipped_quantity', $quantity);
        }

        SalesOrder::where('id', $deliveryOrder->sales_order_id)
            ->whereDoesntHave('items', function ($query) {
                $query->whereColumn('shipped_quantity', '<', 'quantity');
            })
            ->update(['status' => SalesOrderStatus::CLOSED->value]);
    }

    public function changeDeliveryOrderStatus(int $id, string $status): void
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->update(['status' => $status]);
    }

    public function fetchDOItemsForSalesInvoice(int $id): Collection
    {
        $items = DeliveryOrderItem::with(['product:id,code,name,unit_id', 'product.unit:id,name,symbol', 'salesOrderItem:id,unit_price,discount_percentage,discount_amount,total_amount,invoiced_quantity'])
            ->whereHas('deliveryOrder', function ($query) use ($id) {
                $query->where('sales_order_id', $id)
                    ->where('status', DeliveryOrderStatus::FINISHED->value);
            })
            ->orderBy('id', 'asc')
            ->get();

        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'sales_order_item_id' => $item->sales_order_item_id,
                'product_id' => $item->product_id,
                'product_code' => $item->product->code,
                'product_name' => $item->product->name,
                'unit_id' => $item->product->unit_id,
                'unit_symbol' => $item->product->unit->symbol,
                'quantity' => $item->quantity,
                'unit_price' => $item->salesOrderItem->unit_price,
                'discount_percentage' => $item->salesOrderItem->discount_percentage,
                'discount_amount' => $item->salesOrderItem->discount_amount,
            ];
        });
    }
}
