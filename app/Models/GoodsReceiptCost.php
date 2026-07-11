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
    ];

    protected $casts = [
        'amount' => 'double',
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
