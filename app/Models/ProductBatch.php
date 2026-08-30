<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'goods_receipt_item_id',
        'batch_number',
        'supplier_batch_number',
        'initial_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'initial_quantity' => 'double',
        'unit_cost' => 'double',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceiptItem()
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function productBatchStocks()
    {
        return $this->hasMany(ProductBatchStock::class);
    }
}
