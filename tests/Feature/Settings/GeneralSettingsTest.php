<?php

use App\Enums\Currency;
use App\Enums\UserRole;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('administrator can view general settings', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('settings.general.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/general')
            ->has('settings.app_name')
            ->where('settings.currency', 'usd')
            ->where('settings.logo_url', null)
            ->has('currency_options', 2));
});

test('non administrator cannot view general settings', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($user)
        ->get(route('settings.general.edit'))
        ->assertForbidden();
});

test('administrator can update general settings', function () {
    $admin = User::factory()->administrator()->create();

    $response = $this
        ->actingAs($admin)
        ->put(route('settings.general.update'), [
            'app_name' => 'شركة التوزيع',
            'currency' => 'iqd',
            'invoice_header' => 'رأس الفاتورة',
            'invoice_footer' => 'تذييل الفاتورة',
            'receipt_header' => 'رأس السند',
            'receipt_footer' => 'تذييل السند',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.general.edit'));

    $settings = SystemSetting::current();

    expect($settings->app_name)->toBe('شركة التوزيع')
        ->and($settings->currency)->toBe(Currency::Iqd)
        ->and($settings->invoice_header)->toBe('رأس الفاتورة')
        ->and($settings->invoice_footer)->toBe('تذييل الفاتورة')
        ->and($settings->receipt_header)->toBe('رأس السند')
        ->and($settings->receipt_footer)->toBe('تذييل السند');
});

test('administrator can upload and remove a logo', function () {
    Storage::fake('public');

    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->put(route('settings.general.update'), [
            'app_name' => 'SDMS',
            'currency' => 'usd',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.general.edit'));

    $settings = SystemSetting::current();

    expect($settings->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($settings->logo_path);

    $previousPath = $settings->logo_path;

    $this->actingAs($admin)
        ->put(route('settings.general.update'), [
            'app_name' => 'SDMS',
            'currency' => 'usd',
            'remove_logo' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.general.edit'));

    $settings->refresh();

    expect($settings->logo_path)->toBeNull();
    Storage::disk('public')->assertMissing($previousPath);
});

test('general settings require an app name', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->from(route('settings.general.edit'))
        ->put(route('settings.general.update'), [
            'app_name' => '',
            'currency' => 'usd',
        ])
        ->assertSessionHasErrors('app_name')
        ->assertRedirect(route('settings.general.edit'));
});

test('general settings reject an invalid currency', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->from(route('settings.general.edit'))
        ->put(route('settings.general.update'), [
            'app_name' => 'SDMS',
            'currency' => 'eur',
        ])
        ->assertSessionHasErrors('currency')
        ->assertRedirect(route('settings.general.edit'));
});

test('shared inertia props use system settings', function () {
    SystemSetting::query()->create([
        'app_name' => 'اسم مخصص',
        'currency' => Currency::Iqd,
    ]);

    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('settings.general.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('name', 'اسم مخصص')
            ->where('logoUrl', null)
            ->where('currency.code', 'iqd')
            ->where('currency.symbol', 'د.ع')
            ->where('currency.label', 'دينار عراقي'));
});

test('config app name follows system settings', function () {
    SystemSetting::query()->create([
        'app_name' => 'اسم من الإعدادات',
    ]);

    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->put(route('settings.general.update'), [
            'app_name' => 'اسم محدّث',
            'currency' => 'usd',
            'invoice_header' => null,
            'invoice_footer' => null,
            'receipt_header' => null,
            'receipt_footer' => null,
        ])
        ->assertSessionHasNoErrors();

    expect(config('app.name'))->toBe('اسم محدّث')
        ->and(SystemSetting::current()->app_name)->toBe('اسم محدّث');
});

test('system settings default currency is usd', function () {
    $settings = SystemSetting::current();

    expect($settings->currency)->toBe(Currency::Usd);
});
