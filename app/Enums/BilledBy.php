<?php

namespace App\Enums;

enum BilledBy: string
{
    case SUPPLIER = 'supplier';
    case THIRD_PARTY = 'third_party';
    case INTERNAL = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::SUPPLIER => 'Supplier',
            self::THIRD_PARTY => 'Pihak Ketiga',
            self::INTERNAL => 'Internal',
        };
    }

    public static function options(): array
    {
        return [
            self::SUPPLIER->value => self::SUPPLIER->label(),
            self::THIRD_PARTY->value => self::THIRD_PARTY->label(),
            self::INTERNAL->value => self::INTERNAL->label(),
        ];
    }

    public static function dropdownOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'id' => $case->value,
                'name' => $case->label(),
            ])
            ->values()
            ->toArray();
    }

    public static function purchasesDropdownOptions(): array
    {
        // return [
        //     self::SUPPLIER->value => self::SUPPLIER->label(),
        //     self::THIRD_PARTY->value => self::THIRD_PARTY->label(),
        // ];

        return collect([self::SUPPLIER, self::THIRD_PARTY])
            ->map(fn($case) => [
                'id' => $case->value,
                'name' => $case->label(),
            ])
            ->values()
            ->toArray();
    }
}
