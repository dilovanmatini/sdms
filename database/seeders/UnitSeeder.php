<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'كرتون', 'symbol' => 'كرت'],
            ['name' => 'زجاجة', 'symbol' => 'زبج'],
            ['name' => 'علبة', 'symbol' => 'علب'],
            ['name' => 'قطعة', 'symbol' => 'قط'],
            ['name' => 'صندوق', 'symbol' => 'صند'],
        ];

        foreach ($units as $unit) {
            Unit::query()->firstOrCreate(
                ['name' => $unit['name']],
                [
                    'symbol' => $unit['symbol'],
                    'is_active' => true,
                ],
            );
        }
    }
}
