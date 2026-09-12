<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PurchaseOrderItem extends Model
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
                return "Item pesanan pembelian {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'invoiced_quantity',
        'received_quantity',
        'unit_price',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
    ];

    // cast
    protected $casts = [
        'quantity' => 'double',
        'received_quantity' => 'double',
        'invoiced_quantity' => 'double',
        'unit_price' => 'double',
        'subtotal' => 'double',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'tax_percentage' => 'double',
        'tax_amount' => 'double',
        'total_amount' => 'double',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
