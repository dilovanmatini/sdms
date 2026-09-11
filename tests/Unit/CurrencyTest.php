<?php

use App\Enums\Currency;

test('currency labels are arabic names', function () {
    expect(Currency::Usd->label())->toBe('دولار امريكي')
        ->and(Currency::Iqd->label())->toBe('دينار عراقي');
});

test('currency symbols are short display forms', function () {
    expect(Currency::Usd->symbol())->toBe('$')
        ->and(Currency::Iqd->symbol())->toBe('د.ع');
});

test('currency iso codes match money spelling', function () {
    expect(Currency::Usd->isoCode())->toBe('USD')
        ->and(Currency::Iqd->isoCode())->toBe('IQD');
});
