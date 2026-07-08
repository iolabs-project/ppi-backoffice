<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'note',
        'receivable_account_id',
        'payable_account_id',
        'is_customer',
        'is_supplier',
        'is_employee',
        'deleted_at',
    ];
}
