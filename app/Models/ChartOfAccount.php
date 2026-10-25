<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use LogsModelActivity;

    protected string $activityLogName = 'visible';

    protected string $activityLogSubject = 'Akun';

    protected $fillable = [
        'company_id',
        'parent_id',
        'category_id',
        'name',
        'code',
        'note',
        'is_deletable',
        'is_locked',
        'deleted_at',
    ];

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }
}
