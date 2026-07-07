<?php

namespace App\Enums;

enum SalesInvoiceStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::OPEN => 'Open',
            self::PARTIAL => 'Partial',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function options(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->label(),
            self::OPEN->value => self::OPEN->label(),
            self::PARTIAL->value => self::PARTIAL->label(),
            self::PAID->value => self::PAID->label(),
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
