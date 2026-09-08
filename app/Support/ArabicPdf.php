<?php

namespace App\Support;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

/**
 * DomPDF lacks Arabic glyph shaping and BiDi. Reshape Arabic runs with Ar-PHP
 * before rendering, and keep IBM Plex Sans Arabic as the embedded typeface.
 */
final class ArabicPdf
{
    /**
     * @param  view-string  $view
     * @param  array<string, mixed>  $data
     */
    public static function fromView(
        string $view,
        array $data,
        string $paper = 'a4',
        string $orientation = 'portrait',
    ): DomPdf {
        $html = self::reshape(view($view, [
            ...$data,
            'forPdf' => true,
        ])->render());

        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        return Pdf::loadHTML($html)
            ->setPaper($paper, $orientation)
            ->setOption([
                'fontDir' => $fontDir,
                'fontCache' => $fontDir,
                'isRemoteEnabled' => false,
                'defaultFont' => PrintFont::FAMILY,
            ]);
    }

    /**
     * Convert Arabic text runs to presentation-form glyphs DomPDF can paint.
     */
    public static function reshape(string $html): string
    {
        $arabic = new Arabic;
        $positions = $arabic->arIdentify($html);

        for ($i = count($positions) - 1; $i >= 0; $i -= 2) {
            $start = $positions[$i - 1];
            $length = $positions[$i] - $start;
            $glyphs = $arabic->utf8Glyphs(substr($html, $start, $length), 1000, false);

            $html = substr_replace($html, $glyphs, $start, $length);
        }

        return $html;
    }
}
