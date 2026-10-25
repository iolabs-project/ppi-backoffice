<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Shared activity log configuration.
 *
 * Models using this trait must declare:
 * - protected string $activityLogName    ('visible' | 'non_visible')
 * - protected string $activityLogSubject (e.g. 'Produk')
 */
trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->activityLogName)
            ->setDescriptionForEvent(function (string $eventName) {
                $event = match ($eventName) {
                    'created' => 'dibuat',
                    'updated' => 'diperbarui',
                    'deleted' => 'dihapus',
                    default => $eventName,
                };
                return "{$this->activityLogSubject} {$event}";
            })
            ->logAll()
            ->logExcept(array_merge(['password', 'remember_token'], $this->getHidden()))
            ->logOnlyDirty();
    }
}
