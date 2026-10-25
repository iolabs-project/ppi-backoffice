<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'visible';

    protected string $activityLogSubject = 'Penyesuaian stok';

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'number',
        'adjustment_date',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'adjustment_date' => 'datetime:Y-m-d H:i:s',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class, 'stock_adjustment_id');
    }
}
