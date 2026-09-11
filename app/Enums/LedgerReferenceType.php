<?php

namespace App\Enums;

enum LedgerReferenceType: string
{
    case Invoice = 'invoice';
    case Receipt = 'receipt';
    case OpeningBalance = 'opening_balance';

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'فاتورة مبيعات',
            self::Receipt => 'سند قبض',
            self::OpeningBalance => 'مبلغ غير مسدد',
        };
    }
}
