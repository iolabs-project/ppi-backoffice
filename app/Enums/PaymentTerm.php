<?php

namespace App\Enums;

enum PaymentTerm: string
{
    case CASH = 'cash';
    case COD = 'cod';
    case NET_7 = 'net_7';
    case NET_14 = 'net_14';
    case NET_30 = 'net_30';
    case NET_45 = 'net_45';
    case CUSTOM = 'custom';

    // null = due date is picked manually on the document
    public function days(): ?int
    {
        return match ($this) {
            self::CASH, self::COD => 0,
            self::NET_7 => 7,
            self::NET_14 => 14,
            self::NET_30 => 30,
            self::NET_45 => 45,
            self::CUSTOM => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::COD => 'COD',
            self::NET_7 => 'Net 7',
            self::NET_14 => 'Net 14',
            self::NET_30 => 'Net 30',
            self::NET_45 => 'Net 45',
            self::CUSTOM => 'Manual (pilih tanggal)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
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

    public static function day(string $value): ?int
    {
        return self::from($value)->days();
    }

    // Due date for a document dated $from; a manual term keeps $manualDueDate
    public static function dueDate(?string $value, $from, $manualDueDate = null)
    {
        if (!$value) {
            return null;
        }
        $days = self::day($value);

        return $days === null ? $manualDueDate : \Illuminate\Support\Carbon::parse($from)->addDays($days);
    }
}
