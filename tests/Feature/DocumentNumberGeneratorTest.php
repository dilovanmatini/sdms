<?php

use App\Enums\DocumentType;
use App\Services\DocumentNumberGenerator;

test('document number generator produces sequential unique numbers', function () {
    $generator = app(DocumentNumberGenerator::class);

    expect($generator->generate(DocumentType::Purchase))->toBe('PUR-000001')
        ->and($generator->generate(DocumentType::Purchase))->toBe('PUR-000002')
        ->and($generator->generate(DocumentType::Invoice))->toBe('INV-000001')
        ->and($generator->generate(DocumentType::Receipt))->toBe('REC-000001');
});
