<?php

namespace App\Enums;

enum GoodsReceiptStatus: string
{
    case DRAFT = 'draft';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::FINISHED => 'Selesai',
        };
    }

    public static function options(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->label(),
            self::FINISHED->value => self::FINISHED->label(),
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
