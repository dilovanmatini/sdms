<?php

use App\Support\PrintFont;

test('print font faces use http assets for browser and filesystem paths for pdf', function () {
    $browser = PrintFont::faces(forPdf: false);
    $pdf = PrintFont::faces(forPdf: true);
    $resourceRegular = str_replace('\\', '/', resource_path('fonts/IBMPlexSansArabic-Regular.ttf'));

    expect($browser)
        ->toContain("font-family: 'IBM Plex Sans Arabic'")
        ->toContain('/fonts/IBMPlexSansArabic-Regular.ttf')
        ->toContain('font-weight: 600')
        ->toContain('font-weight: 700')
        ->not->toContain($resourceRegular);

    expect($pdf)
        ->toContain("font-family: 'IBM Plex Sans Arabic'")
        ->toContain($resourceRegular);
});

test('print font family stack prefers ibm plex sans arabic', function () {
    expect(PrintFont::FAMILY)->toBe('IBM Plex Sans Arabic')
        ->and(PrintFont::familyStack())->toStartWith("'IBM Plex Sans Arabic'");
});
