<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/confirm-password'),
    );
});

test('password confirmation requires authentication', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('password.confirm'))
        ->post(route('password.confirm.store'), [
            'password' => 'password',
        ])
        ->assertRedirect();

    expect(session('auth.password_confirmed_at'))->not->toBeNull();
});

test('password confirmation fails with incorrect password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('password.confirm'))
        ->post(route('password.confirm.store'), [
            'password' => 'wrong-password',
        ])
        ->assertSessionHasErrors('password');
});

test('confirmed password unlocks security settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('security.edit'))
        ->assertRedirect(route('password.confirm'));

    $this->actingAs($user)
        ->post(route('password.confirm.store'), [
            'password' => 'password',
        ]);

    $this->actingAs($user)
        ->get(route('security.edit'))
        ->assertOk();
});
