<?php

use App\Models\SystemSetting;

test('web app manifest is publicly available with installable fields', function () {
    SystemSetting::current()->update(['app_name' => 'شركة ريبوار']);

    $response = $this->get(route('pwa.manifest'));

    $response
        ->assertOk()
        ->assertJsonPath('name', 'شركة ريبوار')
        ->assertJsonPath('short_name', 'شركة ريبوار')
        ->assertJsonPath('display', 'standalone')
        ->assertJsonPath('start_url', '/dashboard')
        ->assertJsonPath('lang', 'ar')
        ->assertJsonPath('dir', 'rtl')
        ->assertJsonPath('theme_color', '#2563eb');

    expect($response->headers->get('Content-Type'))->toStartWith('application/manifest+json');

    $icons = $response->json('icons');

    expect($icons)->toBeArray()->not->toBeEmpty();
    expect(collect($icons)->pluck('sizes')->all())->toContain('192x192', '512x512');
});

test('service worker script exists for installability', function () {
    $path = public_path('sw.js');

    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toContain("addEventListener('fetch'");
});

test('root layout links the web app manifest', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('rel="manifest"', false)
        ->assertSee('href="/manifest.json"', false)
        ->assertSee('name="theme-color"', false);
});

test('pwa icon assets exist at required sizes', function () {
    expect(file_exists(public_path('icons/icon-192.png')))->toBeTrue();
    expect(file_exists(public_path('icons/icon-512.png')))->toBeTrue();
    expect(file_exists(public_path('apple-touch-icon.png')))->toBeTrue();
});
