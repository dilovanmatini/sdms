<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Models\DocumentSequence;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Default admin credentials (change in production):
     * username: admin
     * password: password
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@sdms.local',
                'password' => Hash::make('password'),
                'role' => UserRole::Administrator,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        foreach (DocumentType::cases() as $type) {
            DocumentSequence::query()->firstOrCreate(
                ['type' => $type],
                ['last_number' => 0],
            );
        }

        $this->call(UnitSeeder::class);
    }
}
