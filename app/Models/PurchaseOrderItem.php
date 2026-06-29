<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'invoiced_quantity',
        'received_quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
    ];

    // cast
    protected $casts = [
        'quantity' => 'double',
        'received_quantity' => 'double',
        'invoiced_quantity' => 'double',
        'unit_price' => 'double',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'tax_percentage' => 'double',
        'tax_amount' => 'double',
        'total_amount' => 'double',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
