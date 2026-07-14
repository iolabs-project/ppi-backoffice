<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderCharge extends Model
{
    protected $fillable = [
        'sales_order_id',
        'account_id',
        'description',
        'amount',
        'is_taxable',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_taxable' => 'boolean',
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
