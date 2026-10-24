<?php

namespace App\Services;

// Rounds discounts and tax to whole rupiah the same way the order/invoice forms do (Math.round),
// so the saved totals match what the user saw before saving
class DocumentTotals
{
    public static function percentOf($amount, $percentage): float
    {
        return round((float) $amount * (float) ($percentage ?? 0) / 100);
    }

    public static function lineDiscount($quantity, $unitPrice, $discountPercentage): float
    {
        return self::percentOf((float) $quantity * (float) $unitPrice, $discountPercentage);
    }

    public static function lineTotal($quantity, $unitPrice, $discountPercentage): float
    {
        return (float) $quantity * (float) $unitPrice - self::lineDiscount($quantity, $unitPrice, $discountPercentage);
    }
}
