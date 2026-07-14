<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderCost extends Model
{
    protected $fillable = [
        'sales_order_id',
        'account_id',
        'description',
        'amount',
        'is_inventory_related',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_inventory_related' => 'boolean',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
