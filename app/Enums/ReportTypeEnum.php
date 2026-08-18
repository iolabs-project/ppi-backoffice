<?php

namespace App\Enums;

enum ReportTypeEnum: string
{
    case BALANCE_SHEET = 'balance-sheet';
    case CASH_FLOW = 'cash-flow';
    case PROFIT_LOSS = 'profit-loss';
    case EXECUTIVE = 'executive';
    case RECEIVABLE = 'receivable';
    case PAYABLE = 'payable';
    case JOURNAL = 'journal';

    public function label(): string
    {
        return match ($this) {
            self::BALANCE_SHEET => 'Neraca',
            self::CASH_FLOW => 'Arus Kas',
            self::PROFIT_LOSS => 'Laba Rugi',
            self::EXECUTIVE => 'Eksekutif',
            self::RECEIVABLE => 'Piutang Dagang',
            self::PAYABLE => 'Utang Dagang',
            self::JOURNAL => 'Jurnal Umum',
        };
    }

    public static function exists(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'), true);
    }
}
