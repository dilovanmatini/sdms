<?php

use App\Support\QuantityDisplay;

test('quantity display trims trailing zeros', function (mixed $input, string $expected) {
    expect(QuantityDisplay::format($input))->toBe($expected);
})->with([
    [5, '5'],
    ['5.000', '5'],
    [12.5, '12.5'],
    ['12.500', '12.5'],
    ['1.250', '1.25'],
    [0, '0'],
    [null, '0'],
]);

test('money display trims trailing zeros at scale 2', function (mixed $input, string $expected) {
    expect(QuantityDisplay::format($input, 2))->toBe($expected);
})->with([
    [1, '1'],
    ['1.00', '1'],
    ['1.000', '1'],
    [1.5, '1.5'],
    ['1.50', '1.5'],
    [2.05, '2.05'],
    ['2.05', '2.05'],
    [0, '0'],
    [null, '0'],
]);
