<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\DatagridPerPage;

test('products index defaults to ten records per page', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();
    $unit = Unit::factory()->create();
    Product::factory()->count(11)->create([
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    $this->actingAs($admin)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/index')
            ->where('products.per_page', 10)
            ->has('products.data', 10)
            ->where('products.total', 11));
});

test('products index accepts allowed per page query and remembers it in a cookie', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();
    $unit = Unit::factory()->create();
    Product::factory()->count(12)->create([
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    $this->actingAs($admin)
        ->get(route('products.index', ['per_page' => 5]))
        ->assertOk()
        ->assertCookie(DatagridPerPage::COOKIE, null, false)
        ->assertInertia(fn ($page) => $page
            ->where('products.per_page', 5)
            ->has('products.data', 5));

    $this->actingAs($admin)
        ->withUnencryptedCookie(
            DatagridPerPage::COOKIE,
            json_encode(['products' => 5], JSON_THROW_ON_ERROR),
        )
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('products.per_page', 5)
            ->has('products.data', 5));
});

test('products index ignores invalid per page values', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('products.index', ['per_page' => 7]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('products.per_page', 10));
});

test('datagrid per page preferences are isolated per grid', function () {
    $admin = User::factory()->administrator()->create();
    Category::factory()->count(12)->create();

    $this->actingAs($admin)
        ->withUnencryptedCookie(
            DatagridPerPage::COOKIE,
            json_encode([
                'products' => 50,
                'categories' => 5,
            ], JSON_THROW_ON_ERROR),
        )
        ->get(route('categories.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('categories.per_page', 5)
            ->has('categories.data', 5));
});
