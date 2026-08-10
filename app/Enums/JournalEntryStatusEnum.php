<?php

namespace App\Enums;

enum JournalEntryStatusEnum: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::POSTED => 'Posted',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function options(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->label(),
            self::POSTED->value => self::POSTED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }

    public static function dropdownOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'id' => $case->value,
                'name' => $case->label(),
            ])
            ->prepend([
                'id' => 'all',
                'name' => 'Semua',
            ])
            ->values()
            ->toArray();
    }
}
