<?php

namespace App\Services\Sales;

use App\Enums\DeliveryOrderStatus;
use App\Models\Company;
use App\Models\DeliveryOrder;
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
            )->withSum('items as total_received_quantity', 'received_quantity')
            ->withSum('items as total_shrinkage_quantity', 'shrinkage_quantity');

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
            )
            ->find($id);
    }
}
