<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Item penyesuaian stok';

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
