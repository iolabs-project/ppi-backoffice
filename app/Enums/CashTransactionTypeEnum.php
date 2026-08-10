<?php

namespace App\Enums;

enum CashTransactionTypeEnum: string
{
    case SEND = 'send';
    case RECEIVE = 'receive';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::SEND => 'Send',
            self::RECEIVE => 'Receive',
            self::TRANSFER => 'Transfer',
        };
    }

    public static function options(): array
    {
        return [
            self::SEND->value => self::SEND->label(),
            self::RECEIVE->value => self::RECEIVE->label(),
            self::TRANSFER->value => self::TRANSFER->label(),
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
