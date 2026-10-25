<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'visible';

    protected string $activityLogSubject = 'Gudang';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'note',
        'deleted_at',
    ];
    
}
