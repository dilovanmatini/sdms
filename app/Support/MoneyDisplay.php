<?php

namespace App\Support;

use App\Enums\Currency;
use App\Models\SystemSetting;

final class MoneyDisplay
{
    /**
     * Format a monetary value for display and append the system currency symbol.
     *
     * When $trim is true, trailing zeros are removed (form/UI/print style).
     * When false, two decimal places are kept (index/report style).
     * When $thousands is true, thousands separators use commas (print style).
     */
    public static function format(mixed $value, bool $trim = false, bool $thousands = false): string
    {
        if ($thousands) {
            $amount = number_format((float) ($value ?? 0), 2, '.', ',');

            if ($trim) {
                $amount = rtrim(rtrim($amount, '0'), '.') ?: '0';
            }
        } elseif ($trim) {
            $amount = QuantityDisplay::format($value, 2);
        } else {
            $amount = number_format((float) ($value ?? 0), 2, '.', '');
        }

        return self::withSymbol($amount);
    }

    /**
     * Append the system currency symbol to an already-formatted amount string.
     */
    public static function withSymbol(string $amount): string
    {
        return $amount.' '.self::symbol();
    }

    public static function symbol(): string
    {
        $currency = SystemSetting::current()->currency;

        return $currency instanceof Currency ? $currency->symbol() : Currency::Usd->symbol();
    }
}
