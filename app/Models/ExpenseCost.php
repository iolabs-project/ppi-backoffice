<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCost extends Model
{
    protected $fillable = [
        'expense_id',
        'account_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
