<?php

namespace App\Enums;

enum ActivityLogEventEnum: String
{
    case DRAFTED = 'drafted';
    case OPENED = 'opened';
    case CANCELLED = 'cancelled';
    case CLOSED = 'closed';
    case POSTED = 'posted';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::DRAFTED => 'Drafted',
            self::OPENED => 'Opened',
            self::CANCELLED => 'Cancelled',
            self::CLOSED => 'Closed',
            self::POSTED => 'Posted',
            self::FINISHED => 'Finished',
        };
    }

    public static function color(ActivityLogEventEnum $event): string
    {
        return match ($event) {
            self::DRAFTED => 'gray',
            self::OPENED => 'blue',
            self::CANCELLED => 'red',
            self::CLOSED => 'black',
            self::POSTED => 'green',
            self::FINISHED => 'purple',
        };
    }
}
