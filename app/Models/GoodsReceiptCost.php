<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptCost extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'account_id',
        'description',
        'amount',
        'billed_by',
        'is_inventory_cost',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_inventory_cost' => 'boolean',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
