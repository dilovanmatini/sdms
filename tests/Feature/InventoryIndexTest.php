<?php

use App\Enums\InventoryReferenceType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('users with view inventory can see stock quantities', function () {
    $admin = User::factory()->administrator()->create();
    $whole = Product::factory()->create(['name_ar' => 'منتج كامل']);
    $fractional = Product::factory()->create(['name_ar' => 'منتج كسري']);

    InventoryTransaction::factory()->create([
        'product_id' => $whole->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 100,
        'quantity_out' => 0,
    ]);

    InventoryTransaction::factory()->create([
        'product_id' => $whole->id,
        'reference_type' => InventoryReferenceType::Sale,
        'quantity_in' => 0,
        'quantity_out' => 25,
    ]);

    InventoryTransaction::factory()->create([
        'product_id' => $fractional->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => '1.250',
        'quantity_out' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('inventory.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('inventory/index')
            ->has('products.data', 2)
            ->where('products.data.0.available_quantity', '75')
            ->where('products.data.0.name_ar', 'منتج كامل')
            ->where('products.data.1.available_quantity', '1.25')
            ->where('products.data.1.name_ar', 'منتج كسري')
            ->where('filters.search', '')
            ->where('filters.category_id', null)
            ->where('filters.stock', ''));
});

test('inventory can be filtered by search category and stock status', function () {
    $admin = User::factory()->administrator()->create();
    $dairy = Category::factory()->create(['name' => 'ألبان']);
    $bakery = Category::factory()->create(['name' => 'مخبوزات']);

    $inStockDairy = Product::factory()->create([
        'name_ar' => 'حليب طازج',
        'code' => 'MLK-1',
        'category_id' => $dairy->id,
    ]);
    $outOfStockDairy = Product::factory()->create([
        'name_ar' => 'جبن أبيض',
        'code' => 'CHS-1',
        'category_id' => $dairy->id,
    ]);
    $inStockBakery = Product::factory()->create([
        'name_ar' => 'خبز صمون',
        'code' => 'BRD-1',
        'category_id' => $bakery->id,
    ]);

    InventoryTransaction::factory()->create([
        'product_id' => $inStockDairy->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 10,
        'quantity_out' => 0,
    ]);

    InventoryTransaction::factory()->create([
        'product_id' => $inStockBakery->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 5,
        'quantity_out' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('inventory.index', ['category_id' => $dairy->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 2)
            ->where('filters.category_id', $dairy->id)
            ->where('selected_category.value', $dairy->id)
            ->where('selected_category.label', 'ألبان')
            ->where('products.data.0.id', $outOfStockDairy->id)
            ->where('products.data.1.id', $inStockDairy->id));

    $this->actingAs($admin)
        ->get(route('inventory.index', [
            'category_id' => $dairy->id,
            'stock' => 'in_stock',
        ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('filters.stock', 'in_stock')
            ->where('products.data.0.id', $inStockDairy->id)
            ->where('products.data.0.available_quantity', '10'));

    $this->actingAs($admin)
        ->get(route('inventory.index', ['stock' => 'out_of_stock']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('filters.stock', 'out_of_stock')
            ->where('products.data.0.id', $outOfStockDairy->id)
            ->where('products.data.0.available_quantity', '0'));

    $this->actingAs($admin)
        ->get(route('inventory.index', ['search' => 'MLK']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('filters.search', 'MLK')
            ->where('products.data.0.id', $inStockDairy->id));
});

test('sales role can lookup categories for inventory filters', function () {
    $user = User::factory()->create(['role' => UserRole::Sales]);
    Category::factory()->create(['name' => 'صنف مبيعات', 'is_active' => true]);

    $this->actingAs($user)
        ->getJson(route('lookups.categories', ['search' => 'مبيعات']))
        ->assertOk();
});

test('accountant cannot view inventory', function () {
    $user = User::factory()->create(['role' => UserRole::Accountant]);

    $this->actingAs($user)
        ->get(route('inventory.index'))
        ->assertForbidden();
});
