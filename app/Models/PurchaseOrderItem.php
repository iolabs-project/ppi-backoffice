<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'received_quantity',
        'invoiced_quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'total_amount',
    ];
}
