<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItemBatch extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Batch item pengiriman barang';

    protected $fillable = [
        'delivery_order_item_id',
        'product_batch_id',
        'quantity',
        'unit_cost',    
    ];

    protected $casts = [
        'quantity' => 'double',
        'unit_cost' => 'double',
    ];

    public function deliveryOrderItem()
    {
        return $this->belongsTo(DeliveryOrderItem::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
