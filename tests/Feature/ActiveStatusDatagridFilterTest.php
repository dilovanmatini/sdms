<?php

use App\Models\Category;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('datagrids can filter records by active status', function (
    string $routeName,
    string $prop,
    callable $seed,
) {
    $admin = User::factory()->administrator()->create();
    [$active, $inactive] = $seed();

    $this->actingAs($admin)
        ->get(route($routeName, ['is_active' => '1']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has($prop.'.data', 1)
            ->where("{$prop}.data.0.id", $active->id)
            ->where('filters.is_active', '1')
            ->has('active_status_options', 3));

    $this->actingAs($admin)
        ->get(route($routeName, ['is_active' => '0']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has($prop.'.data', 1)
            ->where("{$prop}.data.0.id", $inactive->id)
            ->where('filters.is_active', '0'));

    $this->actingAs($admin)
        ->get(route($routeName))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has($prop.'.data', 2)
            ->where('filters.is_active', ''));
})->with([
    'categories' => [
        'categories.index',
        'categories',
        fn (): array => [
            Category::factory()->create(['is_active' => true]),
            Category::factory()->create(['is_active' => false]),
        ],
    ],
    'products' => [
        'products.index',
        'products',
        fn (): array => [
            Product::factory()->create(['is_active' => true]),
            Product::factory()->create(['is_active' => false]),
        ],
    ],
    'suppliers' => [
        'suppliers.index',
        'suppliers',
        fn (): array => [
            Supplier::factory()->create(['is_active' => true]),
            Supplier::factory()->create(['is_active' => false]),
        ],
    ],
    'distributors' => [
        'distributors.index',
        'distributors',
        fn (): array => [
            Distributor::factory()->create(['is_active' => true]),
            Distributor::factory()->create(['is_active' => false]),
        ],
    ],
    'units' => [
        'units.index',
        'units',
        fn (): array => [
            Unit::factory()->create(['is_active' => true]),
            Unit::factory()->create(['is_active' => false]),
        ],
    ],
]);

test('users index can filter by active status', function () {
    $admin = User::factory()->administrator()->create();
    $active = User::factory()->create(['is_active' => true]);
    $inactive = User::factory()->inactive()->create();

    $this->actingAs($admin)
        ->get(route('users.index', ['is_active' => '1']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 2)
            ->where('filters.is_active', '1')
            ->where('users.data', fn ($rows) => collect($rows)->every(fn ($row) => $row['is_active'] === true)
                && collect($rows)->pluck('id')->contains($active->id)
                && collect($rows)->pluck('id')->doesntContain($inactive->id)));

    $this->actingAs($admin)
        ->get(route('users.index', ['is_active' => '0']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.id', $inactive->id)
            ->where('filters.is_active', '0'));
});
