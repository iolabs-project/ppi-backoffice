<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $fillable = [
        'available_quantity',
    ];

    public function getAvailableQuantityAttribute()
    {
        if (!isset($this->attributes['quantity']) && isset($this->attributes['reserved_quantity'])) {
            return 0;
        }

        return $this->attributes['quantity'] - $this->attributes['reserved_quantity'];
    }
}
