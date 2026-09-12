<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PurchaseInvoiceItem extends Model
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
                return "Item invoice pembelian {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'purchase_invoice_id',
        'purchase_order_item_id',
        'goods_receipt_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'total_amount',
    ];

    protected $casts = [
        'quantity' => 'double',
        'unit_price' => 'double',
        'subtotal' => 'double',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'total_amount' => 'double',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function goodsReceiptItem()
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
