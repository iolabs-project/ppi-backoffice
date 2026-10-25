<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class SalesOrderCharge extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Biaya tambahan pesanan penjualan';

    protected $fillable = [
        'sales_order_id',
        'account_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
