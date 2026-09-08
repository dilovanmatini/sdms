<?php

use App\Enums\DocumentStatus;

test('document status labels use descriptive arabic copy', function () {
    expect(DocumentStatus::Draft->label())->toBe('مسودة')
        ->and(DocumentStatus::Posted->label())->toBe('نشط')
        ->and(DocumentStatus::Cancelled->label())->toBe('ملغى');
});
