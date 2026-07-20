<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItemBatch extends Model
{
    protected $fillable = [
        'delivery_order_item_id',
        'product_batch_id',
        'quantity',
        'unit_cost',    
    ];

    protected $casts = [
        'quantity' => 'double',
        'unit_cost' => 'double',
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
