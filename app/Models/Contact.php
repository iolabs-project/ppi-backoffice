<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'visible';

    protected string $activityLogSubject = 'Kontak';

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
        'transportation_cost',
        'receivable_account_id',
        'payable_account_id',
        'is_customer',
        'is_supplier',
        'is_employee',
        'deleted_at',
    ];

    protected $casts = [
        'transportation_cost' => 'double',
        'is_customer' => 'boolean',
        'is_supplier' => 'boolean',
        'is_employee' => 'boolean',
    ];
}
