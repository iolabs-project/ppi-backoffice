<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'category_id',
        'unit_id',
        'code',
        'name',
        'batch_prefix',
        'description',
        'minimum_stock',
        'inventory_account_id',
        'sales_account_id',
        'cogs_account_id',
        'deleted_at',
    ];

    // cast
    protected $casts = [
        'average_unit_cost' => 'double',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }
}
