<?php

namespace App\Enums;

enum AccountSettingEnum: String
{
    case CASH = 'cash';
    case BANK = 'bank';
    case ACCOUNT_RECEIVABLE = 'account_receivable';
    case INVENTORY = 'inventory';
    case INPUT_TAX = 'input_tax';
    case PURCHASE_DOWN_PAYMENT = 'purchase_down_payment';
    case ACCOUNT_PAYABLE = 'account_payable';
    case GRNI = 'grni';
    case OUTPUT_TAX = 'output_tax';
    case SALES_DOWN_PAYMENT = 'sales_down_payment';
    case RETAINED_EARNINGS = 'retained_earnings';
    case OPENING_BALANCE_EQUITY = 'opening_balance_equity';
    case SALES_REVENUE = 'sales_revenue';
    case SALES_DISCOUNT = 'sales_discount';
    case SALES_RETURN = 'sales_return';
    case COGS = 'cogs';
    case PURCHASE_DISCOUNT = 'purchase_discount';
    case PURCHASE_RETURN = 'purchase_return';
    case INVENTORY_ADJUSTMENT_GAIN = 'inventory_adjustment_gain';
    case INVENTORY_ADJUSTMENT_LOSS = 'inventory_adjustment_loss';
    case ROUNDING_GAIN = 'rounding_gain';
    case ROUNDING_LOSS = 'rounding_loss';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Kas',
            self::BANK => 'Rekening Bank',
            self::ACCOUNT_RECEIVABLE => 'Piutang Usaha',
            self::INVENTORY => 'Persediaan Barang',
            self::INPUT_TAX => 'PPN Masukan',
            self::PURCHASE_DOWN_PAYMENT => 'Uang Muka Pembelian',
            self::ACCOUNT_PAYABLE => 'Hutang Usaha',
            self::GRNI => 'Hutang Belum Ditagih (GRNI)',
            self::OUTPUT_TAX => 'PPN Keluaran',
            self::SALES_DOWN_PAYMENT => 'Uang Muka Penjualan',
            self::RETAINED_EARNINGS => 'Laba Ditahan',
            self::OPENING_BALANCE_EQUITY => 'Ekuitas Saldo Awal',
            self::SALES_REVENUE => 'Pendapatan Penjualan',
            self::SALES_DISCOUNT => 'Diskon Penjualan',
            self::SALES_RETURN => 'Retur Penjualan',
            self::COGS => 'Harga Pokok Penjualan (HPP)',
            self::PURCHASE_DISCOUNT => 'Diskon Pembelian',
            self::PURCHASE_RETURN => 'Retur Pembelian',
            self::INVENTORY_ADJUSTMENT_GAIN => 'Penyesuaian Persediaan (Lebih)',
            self::INVENTORY_ADJUSTMENT_LOSS => 'Penyesuaian Persediaan (Kurang)',
            self::ROUNDING_GAIN => 'Selisih Pembulatan (Untung)',
            self::ROUNDING_LOSS => 'Selisih Pembulatan (Rugi)',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::CASH, self::BANK, self::ACCOUNT_RECEIVABLE, self::INVENTORY, self::INPUT_TAX, self::PURCHASE_DOWN_PAYMENT => 'Aset',
            self::ACCOUNT_PAYABLE, self::GRNI, self::OUTPUT_TAX, self::SALES_DOWN_PAYMENT => 'Liabilitas',
            self::RETAINED_EARNINGS, self::OPENING_BALANCE_EQUITY => 'Ekuitas',
            self::SALES_REVENUE, self::SALES_DISCOUNT, self::SALES_RETURN => 'Pendapatan',
            self::COGS, self::PURCHASE_DISCOUNT, self::PURCHASE_RETURN => 'Pembelian & Persediaan',
            self::INVENTORY_ADJUSTMENT_GAIN, self::INVENTORY_ADJUSTMENT_LOSS => 'Penyesuaian Persediaan',
            self::ROUNDING_GAIN, self::ROUNDING_LOSS => 'Pendapatan & Beban Lainnya',
        };
    }

    public static function grouped(): array
    {
        $groups = [];
        foreach (self::cases() as $case) {
            $groups[$case->group()][] = [
                'key' => $case->value,
                'label' => $case->label(),
            ];
        }

        return collect($groups)
            ->map(fn($items, $group) => [
                'group' => $group,
                'items' => $items,
            ])
            ->values()
            ->toArray();
    }
}
