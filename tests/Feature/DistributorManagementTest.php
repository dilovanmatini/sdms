<?php

use App\Enums\UserRole;
use App\Models\Distributor;
use App\Models\SalesInvoice;
use App\Models\User;

test('administrator can manage distributors', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('distributors.store'), [
            'name' => 'موزع الاختبار',
            'contact_person' => 'سامي',
            'phone' => '0750000000',
            'address' => 'السليمانية',
            'credit_limit' => 10000,
            'notes' => null,
            'is_active' => true,
        ])
        ->assertRedirect(route('distributors.index'));

    $this->assertDatabaseHas('distributors', ['name' => 'موزع الاختبار']);
});

test('distributor with invoices cannot be deleted', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create();
    SalesInvoice::factory()->create(['distributor_id' => $distributor->id]);

    $this->actingAs($admin)
        ->delete(route('distributors.destroy', $distributor))
        ->assertForbidden();
});

test('sales role can manage distributors', function () {
    $user = User::factory()->create(['role' => UserRole::Sales]);

    $this->actingAs($user)
        ->get(route('distributors.index'))
        ->assertOk();
});
