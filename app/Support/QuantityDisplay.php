<?php

namespace App\Support;

final class QuantityDisplay
{
    /**
     * Format a numeric value for form/UI display, trimming trailing zeros.
     *
     * Whole numbers render as "5"; fractional values keep significant digits ("1.5", "2.05").
     */
    public static function format(mixed $value, int $scale = 3): string
    {
        $formatted = number_format((float) ($value ?? 0), $scale, '.', '');

        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }
}
