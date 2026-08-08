<?php

use App\Enums\DocumentStatus;
use App\Enums\InventoryReferenceType;
use App\Enums\LedgerReferenceType;
use App\Enums\UserRole;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\Distributor;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use App\Models\User;

test('administrator can create a draft sales invoice with totals', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post(route('sales-invoices.store'), [
            'invoice_date' => '2026-08-07',
            'distributor_id' => $distributor->id,
            'notes' => 'اختبار',
            'discount' => 10,
            'lines' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 50,
                ],
            ],
        ])
        ->assertRedirect(route('sales-invoices.index'));

    $invoice = SalesInvoice::query()->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toStartWith('INV-')
        ->and($invoice->status)->toBe(DocumentStatus::Draft)
        ->and((string) $invoice->subtotal)->toBe('100.00')
        ->and((string) $invoice->discount)->toBe('10.00')
        ->and((string) $invoice->grand_total)->toBe('90.00')
        ->and($invoice->lines)->toHaveCount(1)
        ->and((string) $invoice->lines->first()->line_total)->toBe('100.00');
});

test('posting a sales invoice decreases stock and creates ledger entries', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create();
    $product = Product::factory()->create();

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 100,
        'quantity_out' => 0,
    ]);

    $invoice = SalesInvoice::factory()->create([
        'distributor_id' => $distributor->id,
        'subtotal' => 200,
        'discount' => 20,
        'grand_total' => 180,
        'status' => DocumentStatus::Draft,
    ]);

    SalesInvoiceLine::factory()->create([
        'sales_invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'quantity' => 15,
        'unit_price' => 12,
        'line_total' => 180,
    ]);

    $this->actingAs($admin)
        ->post(route('sales-invoices.post', $invoice))
        ->assertRedirect(route('sales-invoices.index'));

    $invoice->refresh();

    expect($invoice->status)->toBe(DocumentStatus::Posted)
        ->and($invoice->posted_by)->toBe($admin->id)
        ->and($product->stockQuantity())->toBe('85')
        ->and($distributor->balance())->toBe('180');

    $this->assertDatabaseHas('inventory_transactions', [
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Sale->value,
        'reference_id' => $invoice->id,
        'quantity_out' => 15,
    ]);

    $this->assertDatabaseHas('customer_ledger_entries', [
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice->value,
        'reference_id' => $invoice->id,
        'debit' => 180,
        'credit' => 0,
    ]);

    $this->assertDatabaseHas('accounts_receivable_entries', [
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice->value,
        'reference_id' => $invoice->id,
        'debit' => 180,
        'credit' => 0,
    ]);
});

test('cannot post a sales invoice when stock is insufficient', function () {
    $admin = User::factory()->administrator()->create();
    $product = Product::factory()->create();

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 5,
        'quantity_out' => 0,
    ]);

    $invoice = SalesInvoice::factory()->create([
        'subtotal' => 100,
        'discount' => 0,
        'grand_total' => 100,
    ]);

    SalesInvoiceLine::factory()->create([
        'sales_invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10,
        'line_total' => 100,
    ]);

    $this->actingAs($admin)
        ->post(route('sales-invoices.post', $invoice))
        ->assertRedirect();

    expect($invoice->fresh()->status)->toBe(DocumentStatus::Draft)
        ->and(CustomerLedgerEntry::query()->count())->toBe(0)
        ->and(AccountsReceivableEntry::query()->count())->toBe(0)
        ->and($product->stockQuantity())->toBe('5');
});

test('posted sales invoice cannot be updated or deleted', function () {
    $admin = User::factory()->administrator()->create();
    $invoice = SalesInvoice::factory()->posted($admin)->withLines(1)->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->put(route('sales-invoices.update', $invoice), [
            'invoice_date' => '2026-08-08',
            'distributor_id' => $distributor->id,
            'notes' => 'محاولة تعديل',
            'discount' => 0,
            'lines' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10,
                ],
            ],
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('sales-invoices.destroy', $invoice))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('sales-invoices.post', $invoice))
        ->assertForbidden();
});

test('warehouse role cannot manage sales invoices', function () {
    $user = User::factory()->create(['role' => UserRole::Warehouse]);

    $this->actingAs($user)
        ->get(route('sales-invoices.index'))
        ->assertForbidden();
});
