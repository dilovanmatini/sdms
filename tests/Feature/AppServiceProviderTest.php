<?php

use Illuminate\Support\Facades\Process;
use Illuminate\Validation\Rules\Password;

test('artisan package discover works without a sqlite database file', function () {
    $missingPath = sys_get_temp_dir().'/sdms-missing-'.uniqid('', true).'.sqlite';

    expect(file_exists($missingPath))->toBeFalse();

    $result = Process::env([
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => $missingPath,
    ])->run([
        PHP_BINARY,
        base_path('artisan'),
        'package:discover',
        '--no-ansi',
    ]);

    expect($result->successful())->toBeTrue(
        "package:discover failed:\n{$result->errorOutput()}\n{$result->output()}"
    );
});

test('production password defaults require only a minimum of six characters', function () {
    $this->app['env'] = 'production';

    expect(Password::defaults()->toPasswordRulesString())->toBe('minlength: 6;');
});
