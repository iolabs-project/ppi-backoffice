<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'product_batch_id',
        'system_quantity',
        'counted_quantity',
        'difference_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'system_quantity' => 'double',
        'counted_quantity' => 'double',
        'difference_quantity' => 'double',
        'unit_cost' => 'double',
    ];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }
}
