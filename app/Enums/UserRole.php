<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Warehouse = 'warehouse';
    case Sales = 'sales';
    case Accountant = 'accountant';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'مدير النظام',
            self::Warehouse => 'المخزن',
            self::Sales => 'المبيعات',
            self::Accountant => 'المحاسب',
            self::Manager => 'المدير',
        };
    }
}
