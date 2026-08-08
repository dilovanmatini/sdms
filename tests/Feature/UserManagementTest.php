<?php

use App\Enums\UserRole;
use App\Models\User;

test('administrator can create users', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'موظف المخزن',
            'username' => 'warehouse1',
            'email' => 'warehouse1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::Warehouse->value,
            'is_active' => true,
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'warehouse1',
        'role' => UserRole::Warehouse->value,
    ]);
});

test('non-admin cannot manage users', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('administrator cannot delete themselves', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertForbidden();

    expect($admin->fresh())->not->toBeNull();
});

test('administrator can soft delete another user', function () {
    $admin = User::factory()->administrator()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    expect(User::query()->find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull();
});
