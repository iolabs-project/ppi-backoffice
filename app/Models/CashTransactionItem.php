<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransactionItem extends Model
{
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
