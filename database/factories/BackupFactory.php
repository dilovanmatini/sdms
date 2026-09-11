<?php

namespace Database\Factories;

use App\Enums\BackupStatus;
use App\Models\Backup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Backup>
 */
class BackupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => BackupStatus::Pending,
            'disk' => 'local',
            'path' => null,
            'filename' => null,
            'size' => null,
            'error_message' => null,
            'created_by' => null,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BackupStatus::Processing,
        ]);
    }

    public function completed(): static
    {
        $uuid = Str::uuid()->toString();

        return $this->state(fn (array $attributes): array => [
            'status' => BackupStatus::Completed,
            'path' => 'backups/'.$uuid.'.zip',
            'filename' => 'backup-'.now()->format('Y-m-d-His').'.zip',
            'size' => 2048,
            'error_message' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BackupStatus::Failed,
            'path' => null,
            'filename' => null,
            'size' => null,
            'error_message' => 'Dump failed.',
        ]);
    }
}
