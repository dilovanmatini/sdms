<?php

namespace App\Support;

/**
 * Arabic print/PDF typeface used by document Blade views.
 *
 * Browser print must use HTTP-accessible font URLs under /public/fonts.
 * DomPDF needs filesystem paths under resources/fonts.
 */
final class PrintFont
{
    public const FAMILY = 'IBM Plex Sans Arabic';

    /**
     * @return non-empty-string
     */
    public static function familyStack(): string
    {
        return "'".self::FAMILY."', ui-sans-serif, sans-serif";
    }

    /**
     * CSS @font-face rules for Regular / SemiBold / Bold.
     */
    public static function faces(bool $forPdf = false): string
    {
        $faces = [
            400 => 'IBMPlexSansArabic-Regular.ttf',
            600 => 'IBMPlexSansArabic-SemiBold.ttf',
            700 => 'IBMPlexSansArabic-Bold.ttf',
        ];

        $blocks = [];

        foreach ($faces as $weight => $file) {
            $src = $forPdf
                ? str_replace('\\', '/', resource_path('fonts/'.$file))
                : '/fonts/'.$file;

            $family = self::FAMILY;
            $blocks[] = <<<CSS
@font-face {
    font-family: '{$family}';
    font-style: normal;
    font-weight: {$weight};
    font-display: swap;
    src: url('{$src}') format('truetype');
}
CSS;
        }

        return implode("\n", $blocks);
    }
}
