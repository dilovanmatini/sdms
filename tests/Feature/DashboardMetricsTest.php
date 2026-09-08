<?php

use App\Enums\DocumentStatus;
use App\Enums\InventoryReferenceType;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\Distributor;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;

test('dashboard shows aggregated kpis for authenticated users', function () {
    $admin = User::factory()->administrator()->create();
    $product = Product::factory()->create();
    $distributor = Distributor::factory()->create();

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 40,
        'quantity_out' => 0,
    ]);

    SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'invoice_date' => now()->toDateString(),
        'grand_total' => 120,
        'subtotal' => 120,
        'discount' => 0,
        'status' => DocumentStatus::Posted,
    ]);

    AccountsReceivableEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'debit' => 120,
        'credit' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('metrics.current_inventory_units', '40')
            ->where('metrics.today_sales', '120 $')
            ->where('metrics.outstanding_receivables', '120 $')
            ->where('metrics.total_products', 1)
            ->where('metrics.total_customers', 1)
            ->has('metrics.recent_sales', 1)
            ->where('metrics.recent_sales.0.grand_total', '120 $')
            ->where('metrics.recent_sales.0.id', fn ($id) => is_int($id) && $id > 0)
            ->has('metrics.recent_payments'));
});
