<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

test('administrator can manage units from settings', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('units.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('units.create-edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/units/create-edit')
            ->where('unit', null));

    $this->actingAs($admin)
        ->from(route('units.create-edit'))
        ->post(route('units.store-update'), [
            'name' => 'برميل',
            'symbol' => 'برم',
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('units', [
        'name' => 'برميل',
        'symbol' => 'برم',
    ]);
});

test('warehouse cannot manage units', function () {
    $user = User::factory()->create(['role' => UserRole::Warehouse]);

    $this->actingAs($user)
        ->get(route('units.index'))
        ->assertForbidden();
});

test('manager can manage units', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    $this->actingAs($user)
        ->get(route('units.index'))
        ->assertOk();
});

test('unit in use cannot be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $unit = Unit::factory()->create();
    Product::factory()->create(['unit_id' => $unit->id]);

    $this->actingAs($admin)
        ->delete(route('units.destroy', $unit))
        ->assertForbidden();

    expect($unit->fresh())->not->toBeNull();
});

test('unused unit can be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $unit = Unit::factory()->create();

    $this->actingAs($admin)
        ->delete(route('units.destroy', $unit))
        ->assertRedirect(route('units.index'));

    expect(Unit::query()->find($unit->id))->toBeNull();
});

test('product create requires an existing unit', function () {
    $admin = User::factory()->administrator()->create();
    $unit = Unit::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->post(route('products.store-update'), [
            'code' => 'PRD-UOM-1',
            'barcode' => null,
            'name_ar' => 'منتج بوحدة',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'notes' => null,
            'is_active' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('products', [
        'code' => 'PRD-UOM-1',
        'unit_id' => $unit->id,
    ]);
});
