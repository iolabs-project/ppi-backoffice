<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    protected $appends = [
        'redirect_url',
    ];

    public function getRedirectUrlAttribute()
    {
        switch ($this->transaction_type) {
            case 'purchase_order':
                return route('purchasings.purchase_orders.show', $this->transaction_id);
            case 'goods_receipt':
                return route('purchasings.goods_receipts.show', $this->transaction_id);
            case 'purchase_invoice':
                return route('purchasings.purchase_invoices.show', $this->transaction_id);
            default:
                return null;
        }
    }
}
