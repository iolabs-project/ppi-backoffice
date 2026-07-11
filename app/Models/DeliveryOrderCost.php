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
    ];

    protected $casts = [
        'amount' => 'double',
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
