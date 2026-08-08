<?php

use App\Enums\DocumentStatus;
use App\Enums\LedgerReferenceType;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptAllocation;
use App\Models\SalesInvoice;
use App\Models\User;

test('administrator can create a draft payment receipt with allocations', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);
    $invoice = SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'grand_total' => 500,
        'subtotal' => 500,
        'discount' => 0,
    ]);

    $this->actingAs($admin)
        ->post(route('payment-receipts.store'), [
            'receipt_date' => '2026-08-07',
            'distributor_id' => $distributor->id,
            'payment_method' => PaymentMethod::Cash->value,
            'notes' => 'دفعة جزئية',
            'allocations' => [
                [
                    'sales_invoice_id' => $invoice->id,
                    'amount' => 150,
                ],
            ],
        ])
        ->assertRedirect(route('payment-receipts.index'));

    $receipt = PaymentReceipt::query()->first();

    expect($receipt)->not->toBeNull()
        ->and($receipt->number)->toStartWith('REC-')
        ->and($receipt->status)->toBe(DocumentStatus::Draft)
        ->and($receipt->allocations)->toHaveCount(1)
        ->and((string) $receipt->allocations->first()->amount)->toBe('150.00');
});

test('posting a payment receipt creates ledger credits and reduces balance', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create();
    $invoice = SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'grand_total' => 200,
        'subtotal' => 200,
        'discount' => 0,
    ]);

    CustomerLedgerEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'reference_id' => $invoice->id,
        'debit' => 200,
        'credit' => 0,
    ]);

    AccountsReceivableEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'reference_id' => $invoice->id,
        'debit' => 200,
        'credit' => 0,
    ]);

    $receipt = PaymentReceipt::factory()->create([
        'distributor_id' => $distributor->id,
        'payment_method' => PaymentMethod::BankTransfer,
        'status' => DocumentStatus::Draft,
    ]);

    PaymentReceiptAllocation::factory()->create([
        'payment_receipt_id' => $receipt->id,
        'sales_invoice_id' => $invoice->id,
        'amount' => 75,
    ]);

    $this->actingAs($admin)
        ->post(route('payment-receipts.post', $receipt))
        ->assertRedirect(route('payment-receipts.index'));

    $receipt->refresh();

    expect($receipt->status)->toBe(DocumentStatus::Posted)
        ->and($receipt->posted_by)->toBe($admin->id)
        ->and($distributor->balance())->toBe('125')
        ->and($invoice->remainingAmount())->toBe('125.00');

    $this->assertDatabaseHas('customer_ledger_entries', [
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Receipt->value,
        'reference_id' => $receipt->id,
        'debit' => 0,
        'credit' => 75,
    ]);

    $this->assertDatabaseHas('accounts_receivable_entries', [
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Receipt->value,
        'reference_id' => $receipt->id,
        'debit' => 0,
        'credit' => 75,
    ]);
});

test('cannot allocate more than invoice remaining balance', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);
    $invoice = SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'grand_total' => 100,
        'subtotal' => 100,
        'discount' => 0,
    ]);

    $this->actingAs($admin)
        ->post(route('payment-receipts.store'), [
            'receipt_date' => '2026-08-07',
            'distributor_id' => $distributor->id,
            'payment_method' => PaymentMethod::Cash->value,
            'notes' => null,
            'allocations' => [
                [
                    'sales_invoice_id' => $invoice->id,
                    'amount' => 150,
                ],
            ],
        ])
        ->assertSessionHasErrors('allocations.0.amount');

    expect(PaymentReceipt::query()->count())->toBe(0);
});

test('one receipt can allocate across multiple invoices', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);

    $first = SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'grand_total' => 100,
        'subtotal' => 100,
        'discount' => 0,
    ]);
    $second = SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'grand_total' => 80,
        'subtotal' => 80,
        'discount' => 0,
    ]);

    CustomerLedgerEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'reference_id' => $first->id,
        'debit' => 100,
        'credit' => 0,
    ]);
    CustomerLedgerEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'reference_id' => $second->id,
        'debit' => 80,
        'credit' => 0,
    ]);

    $receipt = PaymentReceipt::factory()->create([
        'distributor_id' => $distributor->id,
        'status' => DocumentStatus::Draft,
    ]);

    PaymentReceiptAllocation::factory()->create([
        'payment_receipt_id' => $receipt->id,
        'sales_invoice_id' => $first->id,
        'amount' => 40,
    ]);
    PaymentReceiptAllocation::factory()->create([
        'payment_receipt_id' => $receipt->id,
        'sales_invoice_id' => $second->id,
        'amount' => 80,
    ]);

    $this->actingAs($admin)
        ->post(route('payment-receipts.post', $receipt))
        ->assertRedirect(route('payment-receipts.index'));

    expect($first->remainingAmount())->toBe('60.00')
        ->and($second->remainingAmount())->toBe('0.00')
        ->and($distributor->balance())->toBe('60');
});

test('posted payment receipt cannot be updated or deleted', function () {
    $admin = User::factory()->administrator()->create();
    $receipt = PaymentReceipt::factory()->posted($admin)->withAllocations(1)->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);
    $invoice = SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'grand_total' => 50,
        'subtotal' => 50,
        'discount' => 0,
    ]);

    $this->actingAs($admin)
        ->put(route('payment-receipts.update', $receipt), [
            'receipt_date' => '2026-08-08',
            'distributor_id' => $distributor->id,
            'payment_method' => PaymentMethod::Cash->value,
            'notes' => 'محاولة تعديل',
            'allocations' => [
                [
                    'sales_invoice_id' => $invoice->id,
                    'amount' => 10,
                ],
            ],
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('payment-receipts.destroy', $receipt))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('payment-receipts.post', $receipt))
        ->assertForbidden();
});

test('sales role cannot manage payment receipts', function () {
    $user = User::factory()->create(['role' => UserRole::Sales]);

    $this->actingAs($user)
        ->get(route('payment-receipts.index'))
        ->assertForbidden();
});
