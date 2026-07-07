<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
     protected $fillable = [
        'sales_invoice_id',
        'sales_order_item_id',
        'delivery_order_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'total_amount',
    ];

    protected $casts = [
        'quantity' => 'double',
        'unit_price' => 'double',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'total_amount' => 'double',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function deliveryOrderItem()
    {
        return $this->belongsTo(DeliveryOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
