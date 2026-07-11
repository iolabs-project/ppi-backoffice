<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderCost extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'account_id',
        'description',
        'amount',   
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
