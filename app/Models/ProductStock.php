<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'average_unit_cost',
    ];

    protected $casts = [
        'quantity' => 'double',
        'available_quantity' => 'double',
        'reserved_quantity' => 'double',
        'average_unit_cost' => 'double',
    ];

    public function getAvailableQuantityAttribute()
    {
        if (!isset($this->attributes['quantity']) || !isset($this->attributes['reserved_quantity'])) {
            return null;
        }

        return $this->attributes['quantity'] - $this->attributes['reserved_quantity'];
    }


    protected $appends = [
        'available_quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
