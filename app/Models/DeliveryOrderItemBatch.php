<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItemBatch extends Model
{
    protected $fillable = [
        'delivery_order_item_id',
        'product_batch_id',
        'batch_number',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'double',
    ];

    public function deliveryOrderItem()
    {
        return $this->belongsTo(DeliveryOrderItem::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
