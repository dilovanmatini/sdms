<?php

use App\Enums\DocumentStatus;
use App\Enums\InventoryReferenceType;
use App\Enums\UserRole;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\Supplier;
use App\Models\User;

test('administrator can create a draft purchase with lines', function () {
    $admin = User::factory()->administrator()->create();
    $supplier = Supplier::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post(route('purchases.store'), [
            'purchase_date' => '2026-08-07',
            'supplier_id' => $supplier->id,
            'notes' => 'اختبار',
            'lines' => [
                ['product_id' => $product->id, 'quantity' => 12.5],
            ],
        ])
        ->assertRedirect(route('purchases.index'));

    $purchase = Purchase::query()->first();

    expect($purchase)->not->toBeNull()
        ->and($purchase->number)->toStartWith('PUR-')
        ->and($purchase->status)->toBe(DocumentStatus::Draft)
        ->and($purchase->lines)->toHaveCount(1)
        ->and((string) $purchase->lines->first()->quantity)->toBe('12.500');
});

test('posting a purchase creates inventory transactions and increases stock', function () {
    $admin = User::factory()->administrator()->create();
    $product = Product::factory()->create();
    $purchase = Purchase::factory()
        ->for(Supplier::factory())
        ->create(['status' => DocumentStatus::Draft]);

    PurchaseLine::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 40,
    ]);

    $this->actingAs($admin)
        ->post(route('purchases.post', $purchase))
        ->assertRedirect(route('purchases.index'));

    $purchase->refresh();

    expect($purchase->status)->toBe(DocumentStatus::Posted)
        ->and($purchase->posted_by)->toBe($admin->id)
        ->and($purchase->posted_at)->not->toBeNull();

    $this->assertDatabaseHas('inventory_transactions', [
        'product_id' => $product->id,
        'reference_type' => InventoryReferenceType::Purchase->value,
        'reference_id' => $purchase->id,
        'quantity_in' => 40,
        'quantity_out' => 0,
    ]);

    expect($product->stockQuantity())->toBe('40');
});

test('posted purchase cannot be updated or deleted', function () {
    $admin = User::factory()->administrator()->create();
    $purchase = Purchase::factory()->posted($admin)->withLines(1)->create();
    $supplier = Supplier::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->put(route('purchases.update', $purchase), [
            'purchase_date' => '2026-08-08',
            'supplier_id' => $supplier->id,
            'notes' => 'محاولة تعديل',
            'lines' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('purchases.destroy', $purchase))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('purchases.post', $purchase))
        ->assertForbidden();
});

test('sales role cannot manage purchases', function () {
    $user = User::factory()->create(['role' => UserRole::Sales]);

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertForbidden();
});

test('draft purchase without lines cannot be posted', function () {
    $admin = User::factory()->administrator()->create();
    $purchase = Purchase::factory()->create();

    $this->actingAs($admin)
        ->post(route('purchases.post', $purchase))
        ->assertRedirect();

    expect($purchase->fresh()->status)->toBe(DocumentStatus::Draft)
        ->and(InventoryTransaction::query()->count())->toBe(0);
});

test('updating a draft purchase replaces lines', function () {
    $admin = User::factory()->administrator()->create();
    $supplier = Supplier::factory()->create(['is_active' => true]);
    $oldProduct = Product::factory()->create(['is_active' => true]);
    $newProduct = Product::factory()->create(['is_active' => true]);

    $purchase = Purchase::factory()->create([
        'supplier_id' => $supplier->id,
        'status' => DocumentStatus::Draft,
    ]);

    PurchaseLine::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $oldProduct->id,
        'quantity' => 5,
    ]);

    $this->actingAs($admin)
        ->put(route('purchases.update', $purchase), [
            'purchase_date' => '2026-08-09',
            'supplier_id' => $supplier->id,
            'notes' => null,
            'lines' => [
                ['product_id' => $newProduct->id, 'quantity' => 8],
            ],
        ])
        ->assertRedirect(route('purchases.index'));

    $purchase->refresh()->load('lines');

    expect($purchase->lines)->toHaveCount(1)
        ->and($purchase->lines->first()->product_id)->toBe($newProduct->id)
        ->and((string) $purchase->lines->first()->quantity)->toBe('8.000');
});
