<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

test('administrator can manage categories', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('categories.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->post(route('categories.store'), [
            'name' => 'مشروبات',
            'description' => 'وصف',
            'is_active' => true,
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', ['name' => 'مشروبات']);
});

test('sales role cannot manage categories', function () {
    $user = User::factory()->create(['role' => UserRole::Sales]);

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertForbidden();
});

test('category cannot be deleted when it has products', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $this->actingAs($admin)
        ->delete(route('categories.destroy', $category))
        ->assertForbidden();

    expect($category->fresh())->not->toBeNull();
});

test('unused category can be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    expect(Category::query()->find($category->id))->toBeNull();
});

test('categories can be searched', function () {
    $admin = User::factory()->administrator()->create();
    Category::factory()->create(['name' => 'نبيذ أحمر']);
    Category::factory()->create(['name' => 'بيرة']);

    $this->actingAs($admin)
        ->get(route('categories.index', ['search' => 'نبيذ']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/index')
            ->has('categories.data', 1)
            ->where('categories.data.0.name', 'نبيذ أحمر'));
});
