<?php

use App\Enums\DocumentStatus;
use App\Enums\LedgerReferenceType;
use App\Enums\UserRole;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\Distributor;
use App\Models\OpeningBalance;
use App\Models\PaymentReceipt;
use App\Models\SalesInvoice;
use App\Models\User;

test('administrator can create a draft opening balance', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post(route('opening-balances.store-update'), [
            'entry_date' => '2026-01-01',
            'distributor_id' => $distributor->id,
            'amount' => 750.5,
            'notes' => 'ذمم سابقة',
        ])
        ->assertRedirect(route('opening-balances.create-edit', OpeningBalance::query()->first()));

    $openingBalance = OpeningBalance::query()->first();

    expect($openingBalance)->not->toBeNull()
        ->and($openingBalance->number)->toStartWith('OPB-')
        ->and($openingBalance->status)->toBe(DocumentStatus::Draft)
        ->and((string) $openingBalance->amount)->toBe('750.50')
        ->and($openingBalance->notes)->toBe('ذمم سابقة')
        ->and(CustomerLedgerEntry::query()->count())->toBe(0);
});

test('opening balance create-edit trims trailing zeros on the amount', function () {
    $admin = User::factory()->administrator()->create();
    $openingBalance = OpeningBalance::factory()->create(['amount' => '100.00']);

    $this->actingAs($admin)
        ->get(route('opening-balances.create-edit', $openingBalance))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('opening-balances/create-edit')
            ->where('opening_balance.amount', '100'));
});

test('posting an opening balance creates ledger debits without an invoice', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create();
    $openingBalance = OpeningBalance::factory()->create([
        'distributor_id' => $distributor->id,
        'amount' => 400,
        'status' => DocumentStatus::Draft,
        'entry_date' => '2026-01-15',
    ]);

    $this->actingAs($admin)
        ->post(route('opening-balances.post', $openingBalance))
        ->assertRedirect(route('opening-balances.index'));

    $openingBalance->refresh();

    expect($openingBalance->status)->toBe(DocumentStatus::Posted)
        ->and($openingBalance->posted_by)->toBe($admin->id)
        ->and($distributor->balance())->toBe('400')
        ->and(SalesInvoice::query()->count())->toBe(0);

    $this->assertDatabaseHas('customer_ledger_entries', [
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::OpeningBalance->value,
        'reference_id' => $openingBalance->id,
        'debit' => 400,
        'credit' => 0,
    ]);

    $this->assertDatabaseHas('accounts_receivable_entries', [
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::OpeningBalance->value,
        'reference_id' => $openingBalance->id,
        'debit' => 400,
        'credit' => 0,
    ]);
});

test('cancelling a posted opening balance reverses ledger entries', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create();
    $openingBalance = OpeningBalance::factory()->create([
        'distributor_id' => $distributor->id,
        'amount' => 400,
        'status' => DocumentStatus::Draft,
    ]);

    $this->actingAs($admin)
        ->post(route('opening-balances.post', $openingBalance))
        ->assertRedirect(route('opening-balances.index'));

    expect($distributor->balance())->toBe('400');

    $this->actingAs($admin)
        ->post(route('opening-balances.cancel', $openingBalance))
        ->assertRedirect(route('opening-balances.index'));

    $openingBalance->refresh();

    expect($openingBalance->status)->toBe(DocumentStatus::Cancelled)
        ->and($distributor->balance())->toBe('0');

    $this->assertDatabaseHas('customer_ledger_entries', [
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::OpeningBalance->value,
        'reference_id' => $openingBalance->id,
        'debit' => 0,
        'credit' => 400,
    ]);
});

test('posting a receipt allocates to an older opening balance before later invoices', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);

    $openingBalance = OpeningBalance::factory()->create([
        'distributor_id' => $distributor->id,
        'entry_date' => '2025-12-01',
        'amount' => 100,
        'status' => DocumentStatus::Draft,
    ]);

    $this->actingAs($admin)
        ->post(route('opening-balances.post', $openingBalance))
        ->assertRedirect(route('opening-balances.index'));

    $invoice = SalesInvoice::factory()->posted($admin)->create([
        'distributor_id' => $distributor->id,
        'invoice_date' => '2026-08-02',
        'grand_total' => 80,
        'subtotal' => 80,
        'discount' => 0,
    ]);

    CustomerLedgerEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'reference_id' => $invoice->id,
        'debit' => 80,
        'credit' => 0,
    ]);
    AccountsReceivableEntry::factory()->create([
        'distributor_id' => $distributor->id,
        'reference_type' => LedgerReferenceType::Invoice,
        'reference_id' => $invoice->id,
        'debit' => 80,
        'credit' => 0,
    ]);

    $receipt = PaymentReceipt::factory()->create([
        'distributor_id' => $distributor->id,
        'status' => DocumentStatus::Draft,
        'amount' => 120,
    ]);

    $this->actingAs($admin)
        ->post(route('payment-receipts.post', $receipt))
        ->assertRedirect(route('payment-receipts.index'));

    expect($openingBalance->remainingAmount())->toBe('0.00')
        ->and($invoice->remainingAmount())->toBe('60.00')
        ->and($distributor->balance())->toBe('60')
        ->and($receipt->allocations)->toHaveCount(2)
        ->and($receipt->allocations->first()->opening_balance_id)->toBe($openingBalance->id)
        ->and($receipt->allocations->last()->sales_invoice_id)->toBe($invoice->id);
});

test('cannot cancel an opening balance allocated to a posted receipt', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create();
    $openingBalance = OpeningBalance::factory()->create([
        'distributor_id' => $distributor->id,
        'amount' => 100,
        'status' => DocumentStatus::Draft,
    ]);

    $this->actingAs($admin)
        ->post(route('opening-balances.post', $openingBalance))
        ->assertRedirect(route('opening-balances.index'));

    $receipt = PaymentReceipt::factory()->create([
        'distributor_id' => $distributor->id,
        'amount' => 40,
        'status' => DocumentStatus::Draft,
    ]);

    $this->actingAs($admin)
        ->post(route('payment-receipts.post', $receipt))
        ->assertRedirect(route('payment-receipts.index'));

    $this->actingAs($admin)
        ->from(route('opening-balances.create-edit', $openingBalance))
        ->post(route('opening-balances.cancel', $openingBalance))
        ->assertRedirect(route('opening-balances.create-edit', $openingBalance));

    expect($openingBalance->fresh()->status)->toBe(DocumentStatus::Posted)
        ->and($openingBalance->remainingAmount())->toBe('60.00');
});

test('draft opening balance requires a positive amount', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post(route('opening-balances.store-update'), [
            'entry_date' => '2026-01-01',
            'distributor_id' => $distributor->id,
            'amount' => 0,
            'notes' => null,
        ])
        ->assertSessionHasErrors('amount');

    expect(OpeningBalance::query()->count())->toBe(0);
});

test('posted opening balance cannot be updated or deleted', function () {
    $admin = User::factory()->administrator()->create();
    $openingBalance = OpeningBalance::factory()->posted($admin)->create(['amount' => 50]);
    $distributor = Distributor::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post(route('opening-balances.store-update', $openingBalance), [
            'entry_date' => '2026-01-02',
            'distributor_id' => $distributor->id,
            'amount' => 10,
            'notes' => 'محاولة تعديل',
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('opening-balances.destroy', $openingBalance))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('opening-balances.post', $openingBalance))
        ->assertForbidden();
});

test('cancelled opening balances are hidden from the default index and shown when filtered', function () {
    $admin = User::factory()->administrator()->create();
    $draft = OpeningBalance::factory()->create();
    $posted = OpeningBalance::factory()->posted($admin)->create();
    $cancelled = OpeningBalance::factory()->cancelled($admin)->create();

    $this->actingAs($admin)
        ->get(route('opening-balances.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('opening-balances/index')
            ->has('opening_balances.data', 2)
            ->where('opening_balances.data.0.id', $posted->id)
            ->where('opening_balances.data.1.id', $draft->id)
            ->where('filters.status', ''));

    $this->actingAs($admin)
        ->get(route('opening-balances.index', ['status' => 'cancelled']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('opening-balances/index')
            ->has('opening_balances.data', 1)
            ->where('opening_balances.data.0.id', $cancelled->id)
            ->where('filters.status', 'cancelled'));
});

test('sales and accountant can manage opening balances but warehouse cannot', function () {
    $sales = User::factory()->create(['role' => UserRole::Sales]);
    $accountant = User::factory()->create(['role' => UserRole::Accountant]);
    $warehouse = User::factory()->create(['role' => UserRole::Warehouse]);

    $this->actingAs($sales)
        ->get(route('opening-balances.index'))
        ->assertOk();

    $this->actingAs($accountant)
        ->get(route('opening-balances.index'))
        ->assertOk();

    $this->actingAs($warehouse)
        ->get(route('opening-balances.index'))
        ->assertForbidden();
});

test('opening balance create prefills distributor from query string', function () {
    $admin = User::factory()->administrator()->create();
    $distributor = Distributor::factory()->create(['name' => 'موزع الرصيد']);

    $this->actingAs($admin)
        ->get(route('opening-balances.create-edit', [
            'distributor_id' => $distributor->id,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('opening-balances/create-edit')
            ->where('opening_balance', null)
            ->where('selected_distributor.value', $distributor->id)
            ->where('selected_distributor.label', 'موزع الرصيد'));
});
