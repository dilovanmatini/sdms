<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Models\Concerns\Auditable;
use Database\Factories\OpeningBalanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'number',
    'entry_date',
    'distributor_id',
    'amount',
    'notes',
    'status',
    'posted_at',
    'posted_by',
])]
class OpeningBalance extends Model
{
    /** @use HasFactory<OpeningBalanceFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
            'amount' => 'decimal:2',
            'status' => DocumentStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Distributor, $this>
     */
    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * @return HasMany<PaymentReceiptAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentReceiptAllocation::class);
    }

    public function isPosted(): bool
    {
        return $this->status === DocumentStatus::Posted;
    }

    public function isCancelled(): bool
    {
        return $this->status === DocumentStatus::Cancelled;
    }

    public function isDraft(): bool
    {
        return $this->status === DocumentStatus::Draft;
    }

    public function allocatedAmount(): string
    {
        $result = $this->allocations()
            ->whereHas('paymentReceipt', function ($query): void {
                $query->where('status', DocumentStatus::Posted);
            })
            ->sum('amount');

        return number_format((float) ($result ?? 0), 2, '.', '');
    }

    public function remainingAmount(): string
    {
        return bcsub((string) $this->amount, $this->allocatedAmount(), 2);
    }
}
