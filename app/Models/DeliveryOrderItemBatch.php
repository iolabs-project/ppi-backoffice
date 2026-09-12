<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DeliveryOrderItemBatch extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('non_visible')
            ->setDescriptionForEvent(function (string $eventName) {
                $event = match ($eventName) {
                    'created' => 'dibuat',
                    'updated' => 'diperbarui',
                    'deleted' => 'dihapus',
                    default => $eventName,
                };
                return "Batch item pengiriman barang {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }

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
