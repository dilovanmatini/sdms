<?php

use App\Enums\UserRole;
use App\Models\Distributor;
use App\Models\SalesInvoice;
use App\Models\User;

test('administrator can manage distributors', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('distributors.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('distributors.create-edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('distributors/create-edit')
            ->where('distributor', null));

    $this->actingAs($admin)
        ->from(route('distributors.create-edit'))
        ->post(route('distributors.store-update'), [
            'name' => 'موزع الاختبار',
            'contact_person' => 'سامي',
            'phone' => '0750000000',
            'address' => 'السليمانية',
            'credit_limit' => 10000,
            'notes' => null,
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('distributors', ['name' => 'موزع الاختبار']);

    $distributor = Distributor::query()->where('name', 'موزع الاختبار')->firstOrFail();

    $this->actingAs($admin)
        ->from(route('distributors.create-edit', $distributor))
        ->post(route('distributors.store-update', $distributor), [
            'name' => 'موزع محدث',
            'contact_person' => 'سامي',
            'phone' => '0750000000',
            'address' => 'السليمانية',
            'credit_limit' => 10000,
            'notes' => null,
            'is_active' => false,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('distributors.create-edit', $distributor));

    $this->assertDatabaseHas('distributors', [
        'id' => $distributor->id,
        'name' => 'موزع محدث',
        'is_active' => false,
    ]);
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

test('distributor credit limit must be an integer', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->from(route('distributors.create-edit'))
        ->post(route('distributors.store-update'), [
            'name' => 'موزع بحد ائتمان عشري',
            'contact_person' => null,
            'phone' => null,
            'address' => null,
            'credit_limit' => '10000.50',
            'notes' => null,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('credit_limit')
        ->assertRedirect(route('distributors.create-edit'));

    $this->assertDatabaseMissing('distributors', [
        'name' => 'موزع بحد ائتمان عشري',
    ]);
});
