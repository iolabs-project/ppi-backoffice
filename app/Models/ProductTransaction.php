<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ProductTransaction extends Model
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
                return "Transaksi produk {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }

    protected $appends = [
        'redirect_url',
    ];

    public function getRedirectUrlAttribute()
    {
        switch ($this->transaction_type) {
            case 'purchase_order':
                return route('purchasings.purchase_orders.show', $this->transaction_id);
            case 'goods_receipt':
                return route('purchasings.goods_receipts.show', $this->transaction_id);
            case 'purchase_invoice':
                return route('purchasings.purchase_invoices.show', $this->transaction_id);
            default:
                return null;
        }
    }
}
