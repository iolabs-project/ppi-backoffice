<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'batch_number',
        'expected_quantity',
        'received_quantity',
        'shrinkage_quantity',
        'unit_price',
        'allocated_cost',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'expected_quantity' => 'double',
        'received_quantity' => 'double',
        'shrinkage_quantity' => 'double',
        'unit_price' => 'double',
        'allocated_cost' => 'double',
        'unit_cost' => 'double',
        'total_cost' => 'double',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
