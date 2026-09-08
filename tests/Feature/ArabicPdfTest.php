<?php

use App\Support\ArabicPdf;

test('arabic pdf reshape converts arabic runs to presentation glyphs', function () {
    $html = '<p>كشف حساب</p><span>INV-1</span>';

    $reshaped = ArabicPdf::reshape($html);

    expect($reshaped)
        ->not->toContain('كشف حساب')
        ->toContain('INV-1')
        ->toContain('<p>')
        ->toContain('</p>');
});

test('arabic pdf reshape leaves latin only markup unchanged', function () {
    $html = '<p class="num">100.00 $</p>';

    expect(ArabicPdf::reshape($html))->toBe($html);
});
