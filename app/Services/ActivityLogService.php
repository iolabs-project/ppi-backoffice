<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
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
