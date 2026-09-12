<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PurchaseOrderCost extends Model
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
                return "Biaya pesanan pembelian {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }
    protected $fillable = [
        'purchase_order_id',
        'account_id',
        'description',
        'billed_by',
        'is_inventory_cost',
        'amount',   
    ];

    protected $casts = [
        'amount' => 'double',
        'is_inventory_cost' => 'boolean',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
