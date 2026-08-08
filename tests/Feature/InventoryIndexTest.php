<?php

use App\Enums\InventoryReferenceType;
use App\Enums\UserRole;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;

test('users with view inventory can see stock quantities', function () {
    $admin = User::factory()->administrator()->create();
    $product = Product::factory()->create(['name_ar' => 'منتج المخزون']);

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 100,
        'quantity_out' => 0,
    ]);

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Sale,
        'quantity_in' => 0,
        'quantity_out' => 25,
    ]);

    $this->actingAs($admin)
        ->get(route('inventory.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('inventory/index')
            ->has('products.data', 1)
            ->where('products.data.0.available_quantity', '75')
            ->where('products.data.0.name_ar', 'منتج المخزون'));
});

test('accountant cannot view inventory', function () {
    $user = User::factory()->create(['role' => UserRole::Accountant]);

    $this->actingAs($user)
        ->get(route('inventory.index'))
        ->assertForbidden();
});
