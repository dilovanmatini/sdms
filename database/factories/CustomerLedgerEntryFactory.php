<?php

namespace Database\Factories;

use App\Enums\LedgerReferenceType;
use App\Models\CustomerLedgerEntry;
use App\Models\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerLedgerEntry>
 */
class CustomerLedgerEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'entry_date' => fake()->date(),
            'reference_type' => LedgerReferenceType::Invoice,
            'reference_id' => fake()->numberBetween(1, 1000),
            'debit' => fake()->randomFloat(2, 10, 1000),
            'credit' => 0,
        ];
    }
}
