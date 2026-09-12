<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SalesOrderItem extends Model
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
                return "Item pesanan penjualan {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'invoiced_quantity',
        'shipped_quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
    ];

    // cast
    protected $casts = [
        'quantity' => 'double',
        'invoiced_quantity' => 'double',
        'shipped_quantity' => 'double',
        'unit_price' => 'double',
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
