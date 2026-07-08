<?php

namespace App\Enums;

enum RoleEnum: String
{
    case SUPER_ADMIN = 'Super Admin';
    case ADMIN = 'Admin';
    case ACCOUNTING = 'Accounting';

    public static function valid(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'));
    }
}
