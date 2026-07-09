<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountMapping extends Model
{
    protected $fillable = [
        'company_id',
        'key',
        'account_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
