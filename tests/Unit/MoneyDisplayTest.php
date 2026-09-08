<?php

use App\Enums\Currency;
use App\Models\SystemSetting;
use App\Support\MoneyDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('money display appends the system currency symbol', function () {
    SystemSetting::current()->update(['currency' => Currency::Usd]);

    expect(MoneyDisplay::format(100))->toBe('100.00 $')
        ->and(MoneyDisplay::format(98.95, trim: true))->toBe('98.95 $')
        ->and(MoneyDisplay::format(1200, trim: true, thousands: true))->toBe('1,200 $')
        ->and(MoneyDisplay::withSymbol('50.00'))->toBe('50.00 $');
});

test('money display uses iraqi dinar symbol when configured', function () {
    SystemSetting::current()->update(['currency' => Currency::Iqd]);

    expect(MoneyDisplay::format(25.5))->toBe('25.50 د.ع')
        ->and(MoneyDisplay::symbol())->toBe('د.ع');
});
