<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderCost extends Model
{
    protected $fillable = [
        'delivery_order_id',
        'account_id',
        'description',
        'amount',
        'is_inventory_related',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_inventory_related' => 'boolean',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
