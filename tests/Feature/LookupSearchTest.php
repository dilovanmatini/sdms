<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;

test('authorized users can search lookup endpoints', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create(['name' => 'صنف بحث']);
    $unit = Unit::factory()->create(['name' => 'وحدة بحث']);
    $product = Product::factory()->create([
        'name_ar' => 'منتج بحث',
        'code' => 'LK-1',
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);
    $supplier = Supplier::factory()->create(['name' => 'مورد بحث']);
    $distributor = Distributor::factory()->create(['name' => 'موزع بحث']);

    $this->actingAs($admin)
        ->getJson(route('lookups.products', ['search' => 'بحث']))
        ->assertOk()
        ->assertJsonFragment(['value' => $product->id]);

    $this->actingAs($admin)
        ->getJson(route('lookups.suppliers', ['search' => 'بحث']))
        ->assertOk()
        ->assertJsonFragment(['value' => $supplier->id]);

    $this->actingAs($admin)
        ->getJson(route('lookups.distributors', ['search' => 'بحث']))
        ->assertOk()
        ->assertJsonFragment(['value' => $distributor->id]);

    $this->actingAs($admin)
        ->getJson(route('lookups.categories', ['search' => 'بحث']))
        ->assertOk()
        ->assertJsonFragment(['value' => $category->id]);

    $this->actingAs($admin)
        ->getJson(route('lookups.units', ['search' => 'بحث']))
        ->assertOk()
        ->assertJsonFragment(['value' => $unit->id]);

    $this->actingAs($admin)
        ->getJson(route('lookups.open-invoices', [
            'distributor_id' => $distributor->id,
        ]))
        ->assertOk()
        ->assertJsonStructure(['data']);
});

test('sales role can lookup products for invoices', function () {
    $user = User::factory()->create(['role' => UserRole::Sales]);
    Product::factory()->create(['name_ar' => 'منتج مبيعات', 'is_active' => true]);

    $this->actingAs($user)
        ->getJson(route('lookups.products', ['search' => 'مبيعات']))
        ->assertOk();
});

test('accountant cannot lookup products', function () {
    $user = User::factory()->create(['role' => UserRole::Accountant]);

    $this->actingAs($user)
        ->getJson(route('lookups.products'))
        ->assertForbidden();
});

test('guests cannot access lookups', function () {
    $this->getJson(route('lookups.products'))->assertUnauthorized();
});
