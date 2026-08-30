<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatchStock extends Model
{
    protected $fillable = [
        'product_batch_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'double',
        'reserved_quantity' => 'double',
    ];

    protected $appends = [
        'available_quantity',
    ];

    public function getAvailableQuantityAttribute()
    {
        if (!isset($this->attributes['quantity']) || !isset($this->attributes['reserved_quantity'])) {
            return null;
        }

        return $this->attributes['quantity'] - $this->attributes['reserved_quantity'];
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
