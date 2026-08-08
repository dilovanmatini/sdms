<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['كرتون', 'زجاجة', 'علبة', 'قطعة', 'صندوق', 'لتر']).'-'.fake()->unique()->numerify('###'),
            'symbol' => fake()->optional()->lexify('??'),
            'is_active' => true,
        ];
    }
}
