<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Distributor;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesInvoice>
 */
class SalesInvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $discount = fake()->randomFloat(2, 0, 50);

        return [
            'number' => 'INV-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'invoice_date' => fake()->date(),
            'distributor_id' => Distributor::factory(),
            'notes' => fake()->optional()->sentence(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'grand_total' => $subtotal - $discount,
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

    public function withLines(int $count = 1): static
    {
        return $this->afterCreating(function (SalesInvoice $invoice) use ($count): void {
            SalesInvoiceLine::factory()
                ->count($count)
                ->create(['sales_invoice_id' => $invoice->id]);
        });
    }
}
