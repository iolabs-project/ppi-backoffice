<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'product_batch_id',
        'type',
        'direction',
        'quantity',
        'unit_cost',
        'total_cost',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'transaction_date',
        'note',
    ];

    // cast
    protected $casts = [
        'transaction_date' => 'datetime:Y-m-d H:i',
        'total_cost' => 'double',
        'unit_cost' => 'double',
        'quantity' => 'double',
        'stock_before' => 'double',
        'stock_after' => 'double',
    ];

    // appends
    protected $appends = [
        'reference_redirect',
    ];

    public function reference()
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getReferenceRedirectAttribute()
    {
        if ($this->reference_type && $this->reference_id) {
            switch ($this->reference_type) {
                case GoodsReceipt::class:
                    return route('purchasings.goods_receipts.show', $this->reference_id);
                case PurchaseInvoice::class:
                    return route('purchasings.purchase_invoices.show', $this->reference_id);
                case DeliveryOrder::class:
                    return route('sales.delivery_orders.show', $this->reference_id);
                default:
                    return null;
            }
        }

        return null;
    }
}
