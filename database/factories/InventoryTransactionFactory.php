<?php

namespace Database\Factories;

use App\Enums\InventoryReferenceType;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryTransaction>
 */
class InventoryTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'transaction_date' => fake()->date(),
            'reference_type' => InventoryReferenceType::Purchase,
            'reference_id' => fake()->numberBetween(1, 1000),
            'quantity_in' => fake()->randomFloat(3, 1, 100),
            'quantity_out' => 0,
        ];
    }
}
