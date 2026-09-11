<?php

use App\Enums\UserRole;
use App\Http\Requests\StoreUpdateCategoryRequest;
use App\Http\Requests\StoreUpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

test('administrator can manage categories', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('categories.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('categories.create-edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/create-edit')
            ->where('category', null));

    $this->actingAs($admin)
        ->from(route('categories.create-edit'))
        ->post(route('categories.store-update'), [
            'name' => 'مشروبات',
            'description' => 'وصف',
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['name' => 'مشروبات']);

    $category = Category::query()->where('name', 'مشروبات')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('categories.create-edit', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/create-edit')
            ->where('category.id', $category->id)
            ->where('category.name', 'مشروبات'));

    $this->actingAs($admin)
        ->from(route('categories.create-edit', $category))
        ->post(route('categories.store-update', $category), [
            'name' => 'مشروبات محدثة',
            'description' => 'وصف محدث',
            'is_active' => false,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('categories.create-edit', $category));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'مشروبات محدثة',
        'is_active' => false,
    ]);
});

test('category and product forms label the group field as group', function () {
    expect((new StoreUpdateCategoryRequest)->attributes()['name'])->toBe('اسم گروپ')
        ->and((new StoreUpdateProductRequest)->attributes()['category_id'])->toBe('گروپ');
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
