<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'warehouse_id',
        'product_id',
        'product_batch_id',
        'type',
        'direction',
        'quantity',
        'unit_cost',
        'total_cost',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'transaction_date',
        'note',
    ];
}
