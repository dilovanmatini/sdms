<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PRD-####')),
            'barcode' => fake()->optional()->ean13(),
            'name_ar' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
