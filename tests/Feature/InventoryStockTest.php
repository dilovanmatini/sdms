<?php

use App\Enums\InventoryReferenceType;
use App\Enums\LedgerReferenceType;
use App\Models\CustomerLedgerEntry;
use App\Models\Distributor;
use App\Models\InventoryTransaction;
use App\Models\Product;

test('product stock is calculated from inventory transactions', function () {
    $product = Product::factory()->create();

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Purchase,
        'quantity_in' => 100,
        'quantity_out' => 0,
    ]);

    InventoryTransaction::factory()->create([
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Sale,
        'quantity_in' => 0,
        'quantity_out' => 35,
    ]);

    expect($product->stockQuantity())->toBe('65')
        ->and(Product::stockQuantityFor($product->id))->toBe('65');
});

test('distributor balance is calculated from customer ledger entries', function () {
    $distributor = Distributor::factory()->create();

    CustomerLedgerEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'debit' => 500,
        'credit' => 0,
    ]);

    CustomerLedgerEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Receipt,
        'debit' => 0,
        'credit' => 150,
    ]);

    expect($distributor->balance())->toBe('350')
        ->and(Distributor::balanceFor($distributor->id))->toBe('350');
});
