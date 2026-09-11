<?php

namespace App\Enums;

enum Currency: string
{
    case Usd = 'usd';
    case Iqd = 'iqd';

    public function label(): string
    {
        return match ($this) {
            self::Usd => 'دولار امريكي',
            self::Iqd => 'دينار عراقي',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Usd => '$',
            self::Iqd => 'د.ع',
        };
    }

    public function isoCode(): string
    {
        return match ($this) {
            self::Usd => 'USD',
            self::Iqd => 'IQD',
        };
    }
}
