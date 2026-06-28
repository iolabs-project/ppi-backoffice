<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'goods_receipt_id',
        'batch_number',
        'supplier_batch_number',
        'quantity',
        'reserved_quantity',
        'unit_cost',
    ];

    public function getAvailableQuantityAttribute()
    {
        if (!isset($this->attributes['quantity']) && isset($this->attributes['reserved_quantity'])) {
            return 0;
        }

        return $this->attributes['quantity'] - $this->attributes['reserved_quantity'];
    }
}
