<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderCost extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Biaya pengiriman barang';

    protected $fillable = [
        'delivery_order_id',
        'account_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
