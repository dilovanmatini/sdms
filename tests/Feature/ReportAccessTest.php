<?php

use App\Enums\DocumentStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;

test('manager can open reports hub and inventory report', function () {
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    Product::factory()->create(['name_ar' => 'منتج التقرير']);

    $this->actingAs($manager)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('reports/index')
            ->has('reports'));

    $this->actingAs($manager)
        ->get(route('reports.show', 'inventory'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('reports/show')
            ->where('report.title', 'تقرير المخزون')
            ->has('report.rows', 1));
});

test('sales report excel and pdf exports succeed', function () {
    $admin = User::factory()->administrator()->create();

    SalesInvoice::factory()->posted($admin)->create([
        'invoice_date' => '2026-08-01',
        'grand_total' => 80,
        'subtotal' => 80,
        'discount' => 0,
        'status' => DocumentStatus::Posted,
    ]);

    $this->actingAs($admin)
        ->get(route('reports.excel', 'sales'))
        ->assertSuccessful()
        ->assertHeader('content-disposition');

    $this->actingAs($admin)
        ->get(route('reports.pdf', 'sales'))
        ->assertSuccessful()
        ->assertHeader('content-type', 'application/pdf');

    $this->actingAs($admin)
        ->get(route('reports.print', 'sales'))
        ->assertSuccessful()
        ->assertSee('تقرير المبيعات');
});

test('warehouse role cannot access reports', function () {
    $user = User::factory()->create(['role' => UserRole::Warehouse]);

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertForbidden();
});
