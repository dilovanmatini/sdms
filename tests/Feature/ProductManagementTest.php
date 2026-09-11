<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

test('administrator can manage products', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();
    $unit = Unit::factory()->create();

    $this->actingAs($admin)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/index')
            ->where('products.per_page', 10));

    $this->actingAs($admin)
        ->get(route('products.create-edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/create-edit')
            ->where('product', null));

    $this->actingAs($admin)
        ->from(route('products.create-edit'))
        ->post(route('products.store-update'), [
            'code' => 'PRD-100',
            'barcode' => null,
            'name_ar' => 'منتج تجريبي',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'notes' => null,
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('products', [
        'code' => 'PRD-100',
        'name_ar' => 'منتج تجريبي',
        'unit_id' => $unit->id,
    ]);

    $product = Product::query()->where('code', 'PRD-100')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('products.create-edit', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/create-edit')
            ->where('product.id', $product->id)
            ->where('product.name_ar', 'منتج تجريبي'));

    $this->actingAs($admin)
        ->from(route('products.create-edit', $product))
        ->post(route('products.store-update', $product), [
            'code' => 'PRD-100',
            'barcode' => '123456',
            'name_ar' => 'منتج محدث',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'notes' => 'ملاحظة',
            'is_active' => false,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('products.create-edit', $product));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name_ar' => 'منتج محدث',
        'is_active' => false,
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

test('product code and barcode are optional', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();
    $unit = Unit::factory()->create();

    $this->actingAs($admin)
        ->from(route('products.create-edit'))
        ->post(route('products.store-update'), [
            'name_ar' => 'منتج بدون رمز',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'notes' => null,
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('products', [
        'name_ar' => 'منتج بدون رمز',
        'code' => null,
        'barcode' => null,
    ]);

    $this->actingAs($admin)
        ->from(route('products.create-edit'))
        ->post(route('products.store-update'), [
            'code' => '',
            'barcode' => '',
            'name_ar' => 'منتج ثان بدون رمز',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'notes' => null,
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(Product::query()->whereNull('code')->count())->toBe(2);
});

test('product code must be unique when provided', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();
    $unit = Unit::factory()->create();
    Product::factory()->create(['code' => 'DUP-1']);

    $this->actingAs($admin)
        ->from(route('products.create-edit'))
        ->post(route('products.store-update'), [
            'code' => 'DUP-1',
            'name_ar' => 'منتج مكرر',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('code');
});

test('unused product can be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $product = Product::factory()->create();

    $this->actingAs($admin)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/index')
            ->where('products.data.0.id', $product->id)
            ->where('products.data.0.can_delete', true));

    $this->actingAs($admin)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect(Product::query()->find($product->id))->toBeNull();
});

test('product with inventory history cannot be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $product = Product::factory()->create();

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'quantity_in' => '5.000',
        'quantity_out' => '0.000',
    ]);

    $this->actingAs($admin)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('products/index')
            ->where('products.data.0.id', $product->id)
            ->where('products.data.0.can_delete', false));

    $this->actingAs($admin)
        ->delete(route('products.destroy', $product))
        ->assertForbidden();

    expect($product->fresh())->not->toBeNull();
});
