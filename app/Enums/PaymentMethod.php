<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً',
            self::BankTransfer => 'تحويل بنكي',
            self::Cheque => 'شيك',
            self::Other => 'أخرى',
        };
    }
}
