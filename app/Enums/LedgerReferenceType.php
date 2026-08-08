<?php

namespace App\Enums;

enum LedgerReferenceType: string
{
    case Invoice = 'invoice';
    case Receipt = 'receipt';

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'فاتورة مبيعات',
            self::Receipt => 'سند قبض',
        };
    }
}
