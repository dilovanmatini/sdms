<?php

use App\Enums\UserRole;
use App\Models\User;

test('settings hub is reachable and shows system cards only', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/index')
            ->has('cards', 3)
            ->where('cards.0.title', 'عام')
            ->where('cards.1.title', 'وحدات القياس')
            ->where('cards.2.title', 'المستخدمون')
            ->where('cards', fn ($cards) => collect($cards)->contains('title', 'الملف الشخصي') === false));
});

test('settings hub hides users card for non-admin', function () {
    $user = User::factory()->create(['role' => UserRole::Warehouse]);

    $this->actingAs($user)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/index')
            ->where('cards', fn ($cards) => collect($cards)->contains('title', 'المستخدمون') === false)
            ->where('cards', fn ($cards) => collect($cards)->contains('title', 'عام') === false));
});

test('users are managed under settings path', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('settings/users/index'));

    expect(route('users.index', absolute: false))->toBe('/settings/users');
});

test('legacy users path redirects to settings users', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get('/users')
        ->assertRedirect('/settings/users');
});

test('account settings pages remain under settings path', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('settings/profile'));

    expect(route('profile.edit', absolute: false))->toBe('/settings/profile');
    expect(route('security.edit', absolute: false))->toBe('/settings/security');
    expect(route('appearance.edit', absolute: false))->toBe('/settings/appearance');
});
