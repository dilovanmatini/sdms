<?php

use App\Enums\UserRole;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;

test('administrator can manage suppliers', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('suppliers.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('suppliers.create-edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('suppliers/create-edit')
            ->where('supplier', null));

    $this->actingAs($admin)
        ->from(route('suppliers.create-edit'))
        ->post(route('suppliers.store-update'), [
            'name' => 'مورد الاختبار',
            'contact_person' => 'أحمد',
            'phone' => '0700000000',
            'address' => 'أربيل',
            'notes' => null,
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('suppliers', ['name' => 'مورد الاختبار']);

    $supplier = Supplier::query()->where('name', 'مورد الاختبار')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('suppliers.create-edit', $supplier))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('suppliers/create-edit')
            ->where('supplier.id', $supplier->id)
            ->where('supplier.name', 'مورد الاختبار'));

    $this->actingAs($admin)
        ->from(route('suppliers.create-edit', $supplier))
        ->post(route('suppliers.store-update', $supplier), [
            'name' => 'مورد محدث',
            'contact_person' => 'أحمد',
            'phone' => '0700000000',
            'address' => 'أربيل',
            'notes' => null,
            'is_active' => false,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('suppliers.create-edit', $supplier));

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'مورد محدث',
        'is_active' => false,
    ]);
});

test('supplier with purchases cannot be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $supplier = Supplier::factory()->create();
    Purchase::factory()->create(['supplier_id' => $supplier->id]);

    $this->actingAs($admin)
        ->delete(route('suppliers.destroy', $supplier))
        ->assertForbidden();
});

test('sales role cannot manage suppliers', function () {
    $user = User::factory()->create(['role' => UserRole::Sales]);

    $this->actingAs($user)
        ->get(route('suppliers.index'))
        ->assertForbidden();
});
