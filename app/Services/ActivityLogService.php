<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function fetchTableData(Request $request)
    {
        $user = Auth::user();
        $query = Activity::with(['causer'])
            ->select(
                'id',
                'description',
                'causer_type',
                'causer_id',
                'created_at'
            )
            ->inLog('visible');

        if (!$user->hasRole('Super Admin')) {
            $query->causedBy($user);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }
        
        $query = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));
        return $query;
    }
    public function store(Model $performedOn, string $event, string $description)
    {
        $properties = [];

        if ($performedOn->wasChanged()) {
            $properties = $performedOn->getChanges();
            dd($properties);
        }

        activity()->performedOn($performedOn)
            ->event($event)
            ->log($description);
    }
}
