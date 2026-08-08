<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

test('administrator can create a product', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();
    $unit = Unit::factory()->create();

    $this->actingAs($admin)
        ->post(route('products.store'), [
            'code' => 'PRD-100',
            'barcode' => null,
            'name_ar' => 'منتج تجريبي',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'notes' => null,
            'is_active' => true,
        ])
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'code' => 'PRD-100',
        'name_ar' => 'منتج تجريبي',
        'unit_id' => $unit->id,
    ]);
});

test('warehouse role can manage products', function () {
    $user = User::factory()->create(['role' => UserRole::Warehouse]);

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertOk();
});

test('accountant cannot manage products', function () {
    $user = User::factory()->create(['role' => UserRole::Accountant]);

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertForbidden();
});

test('unused product can be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $product = Product::factory()->create();

    $this->actingAs($admin)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect(Product::query()->find($product->id))->toBeNull();
});
