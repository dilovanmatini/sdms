<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

test('admin seeder creates an active administrator', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('username', 'admin')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe(UserRole::Administrator)
        ->and($admin->is_active)->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeTrue();

    $this->post(route('login.store'), [
        'username' => 'admin',
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($admin);
});
