<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DeliveryOrderCost extends Model
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
                return "Biaya pengiriman barang {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'delivery_order_id',
        'account_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
