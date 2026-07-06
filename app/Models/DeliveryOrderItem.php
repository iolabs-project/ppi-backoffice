<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItem extends Model
{
    // fillable
    protected $fillable = [
        'delivery_order_id',
        'sales_order_item_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'double',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function batches()
    {
        return $this->hasMany(DeliveryOrderItemBatch::class);
    }
}
