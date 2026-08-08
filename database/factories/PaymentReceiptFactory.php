<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Enums\PaymentMethod;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptAllocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentReceipt>
 */
class PaymentReceiptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'REC-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'receipt_date' => fake()->date(),
            'distributor_id' => Distributor::factory(),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
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

    public function withAllocations(int $count = 1): static
    {
        return $this->afterCreating(function (PaymentReceipt $receipt) use ($count): void {
            PaymentReceiptAllocation::factory()
                ->count($count)
                ->create(['payment_receipt_id' => $receipt->id]);
        });
    }
}
