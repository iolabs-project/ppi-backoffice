<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class GoodsReceiptItem extends Model
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
                return "Item penerimaan barang {$event}";
            })
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'batch_number',
        'expected_quantity',
        'received_quantity',
        'shrinkage_quantity',
        'unit_price',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'unit_cost',
        'total_amount',
    ];

    protected $casts = [
        'expected_quantity' => 'double',
        'received_quantity' => 'double',
        'shrinkage_quantity' => 'double',
        'unit_price' => 'double',
        'subtotal' => 'double',
        'discount_percentage' => 'double',
        'discount_amount' => 'double',
        'unit_cost' => 'double',
        'total_amount' => 'double',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function purchaseInvoiceItems()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function purchaseInvoice() 
    {
        return $this->hasOneThrough(PurchaseInvoice::class, PurchaseInvoiceItem::class, 'goods_receipt_item_id', 'id', 'id', 'purchase_invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
