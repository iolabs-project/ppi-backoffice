<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    protected $fillable = [
        'purchase_invoice_id',
        'purchase_order_item_id',
        'goods_receipt_item_id',
        'product_id',
        'quantity',
        'unit_cost',
        'discount_percentage',
        'discount_amount',
        'total_amount',
    ];

    
}
