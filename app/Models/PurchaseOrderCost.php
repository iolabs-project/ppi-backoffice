<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderCost extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Biaya pesanan pembelian';
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
