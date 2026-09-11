<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\DistributorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'contact_person', 'phone', 'address', 'credit_limit', 'notes', 'is_active'])]
class Distributor extends Model
{
    /** @use HasFactory<DistributorFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_limit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<SalesInvoice, $this>
     */
    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    /**
     * @return HasMany<PaymentReceipt, $this>
     */
    public function paymentReceipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    /**
     * @return HasMany<OpeningBalance, $this>
     */
    public function openingBalances(): HasMany
    {
        return $this->hasMany(OpeningBalance::class);
    }

    /**
     * @return HasMany<CustomerLedgerEntry, $this>
     */
    public function customerLedgerEntries(): HasMany
    {
        return $this->hasMany(CustomerLedgerEntry::class);
    }

    public function balance(): string
    {
        $result = $this->customerLedgerEntries()
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        return (string) ($result ?? '0');
    }

    public static function balanceFor(int $distributorId): string
    {
        $result = DB::table('customer_ledger_entries')
            ->where('distributor_id', $distributorId)
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        return (string) ($result ?? '0');
    }
}
