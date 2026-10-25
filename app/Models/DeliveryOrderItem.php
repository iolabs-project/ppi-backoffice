<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItem extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Item pengiriman barang';

    // fillable
    protected $fillable = [
        'delivery_order_id',
        'sales_order_item_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'double',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batches()
    {
        return $this->hasMany(DeliveryOrderItemBatch::class);
    }
}
