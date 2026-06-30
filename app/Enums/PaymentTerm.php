<?php

namespace App\Enums;

enum PaymentTerm: string
{
    case NET_7 = 'net_7';
    case NET_14 = 'net_14';
    case NET_30 = 'net_30';
    case NET_45 = 'net_45';

    public function days(): int
    {
        return match ($this) {
            self::NET_7 => 7,
            self::NET_14 => 14,
            self::NET_30 => 30,
            self::NET_45 => 45,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NET_7 => 'Net 7',
            self::NET_14 => 'Net 14',
            self::NET_30 => 'Net 30',
            self::NET_45 => 'Net 45',
        };
    }

    public static function options(): array
    {
        return [
            self::NET_7->value => self::NET_7->label(),
            self::NET_14->value => self::NET_14->label(),
            self::NET_30->value => self::NET_30->label(),
            self::NET_45->value => self::NET_45->label(),
        ];
    }

    public static function dropdownOptions(): array
    {
        return collect(self::cases())
            ->map(fn($case) => [
                'id' => $case->value,
                'name' => $case->label(),
                'days' => $case->days(),
            ])
            ->values()
            ->toArray();
    }

    public static function day(string $value): int
    {
        return self::from($value)->days();
    }
}
