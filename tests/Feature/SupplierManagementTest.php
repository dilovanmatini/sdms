<?php

use App\Enums\UserRole;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;

test('administrator can manage suppliers', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('suppliers.store'), [
            'name' => 'مورد الاختبار',
            'contact_person' => 'أحمد',
            'phone' => '0700000000',
            'address' => 'أربيل',
            'notes' => null,
            'is_active' => true,
        ])
        ->assertRedirect(route('suppliers.index'));

    $this->assertDatabaseHas('suppliers', ['name' => 'مورد الاختبار']);
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
