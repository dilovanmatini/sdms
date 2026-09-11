<?php

use App\Models\Distributor;
use App\Models\User;

test('sales invoice create prefills distributor from query string', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['name' => 'موزع الاختصار']);

    $this->actingAs($admin)
        ->get(route('sales-invoices.create-edit', [
            'distributor_id' => $distributor->id,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('sales-invoices/create-edit')
            ->where('invoice', null)
            ->where('selected_distributor.value', $distributor->id)
            ->where('selected_distributor.label', 'موزع الاختصار'));
});

test('payment receipt create prefills distributor from query string', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['name' => 'موزع السند']);

    $this->actingAs($admin)
        ->get(route('payment-receipts.create-edit', [
            'distributor_id' => $distributor->id,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('payment-receipts/create-edit')
            ->where('receipt', null)
            ->where('selected_distributor.value', $distributor->id)
            ->where('selected_distributor.label', 'موزع السند')
            ->where('selected_distributor.meta.balance', '0.00'));
});

test('distributor lookup includes contact meta for quick search', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create([
        'name' => 'موزع بحث سريع',
        'contact_person' => 'أحمد',
        'phone' => '07501234567',
    ]);

    $this->actingAs($admin)
        ->getJson(route('lookups.distributors', ['search' => 'سريع']))
        ->assertOk()
        ->assertJsonFragment([
            'value' => $distributor->id,
            'label' => 'موزع بحث سريع',
            'meta' => [
                'contact_person' => 'أحمد',
                'phone' => '07501234567',
                'balance' => '0.00',
            ],
        ]);
});

test('opening balance create prefills distributor from query string', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['name' => 'موزع الرصيد السابق']);

    $this->actingAs($admin)
        ->get(route('opening-balances.create-edit', [
            'distributor_id' => $distributor->id,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('opening-balances/create-edit')
            ->where('opening_balance', null)
            ->where('selected_distributor.value', $distributor->id)
            ->where('selected_distributor.label', 'موزع الرصيد السابق'));
});

test('create pages ignore unknown distributor query ids', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('sales-invoices.create-edit', [
            'distributor_id' => 999999,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('sales-invoices/create-edit')
            ->where('selected_distributor', null));
});
