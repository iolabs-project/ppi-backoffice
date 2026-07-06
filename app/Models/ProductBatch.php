<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'goods_receipt_item_id',
        'batch_number',
        'supplier_batch_number',
        'quantity',
        'reserved_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'double',
        'reserved_quantity' => 'double',
        'unit_cost' => 'double',
    ];
    protected $appends = [
        'available_quantity',
    ];

    public function getAvailableQuantityAttribute()
    {
        if (!isset($this->attributes['quantity']) && isset($this->attributes['reserved_quantity'])) {
            return 0;
        }

        return $this->attributes['quantity'] - $this->attributes['reserved_quantity'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function goodsReceiptItem()
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }
}
