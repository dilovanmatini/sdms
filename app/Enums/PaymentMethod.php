<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Fib = 'fib';
    case QiCard = 'qi_card';
    case FastPay = 'fastpay';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً',
            self::Fib => 'FIB',
            self::QiCard => 'Qi Card',
            self::FastPay => 'FastPay',
            self::BankTransfer => 'تحويل بنكي',
            self::Cheque => 'شيك',
            self::Other => 'أخرى',
        };
    }
}
