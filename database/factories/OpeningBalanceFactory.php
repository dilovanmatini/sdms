<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Distributor;
use App\Models\OpeningBalance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpeningBalance>
 */
class OpeningBalanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'OPB-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'entry_date' => fake()->date(),
            'distributor_id' => Distributor::factory(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'notes' => fake()->optional()->sentence(),
            'status' => DocumentStatus::Draft,
        ];
    }

    public function posted(?User $poster = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DocumentStatus::Posted,
            'posted_at' => now(),
            'posted_by' => $poster?->id ?? User::factory(),
        ]);
    }

    public function cancelled(?User $poster = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => DocumentStatus::Cancelled,
            'posted_at' => now(),
            'posted_by' => $poster?->id ?? User::factory(),
        ]);
    }
}
