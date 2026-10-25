<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class CashTransactionCost extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'non_visible';

    protected string $activityLogSubject = 'Biaya transaksi kas';

    protected $fillable = [
        'cash_transaction_id',
        'account_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function cashTransaction()
    {
        return $this->belongsTo(CashTransaction::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
