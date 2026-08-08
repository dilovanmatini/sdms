<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Models\Concerns\Auditable;
use Database\Factories\SalesInvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'number',
    'invoice_date',
    'distributor_id',
    'notes',
    'subtotal',
    'discount',
    'grand_total',
    'status',
    'posted_at',
    'posted_by',
    'warehouse_id',
    'branch_id',
])]
class SalesInvoice extends Model
{
    /** @use HasFactory<SalesInvoiceFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'posted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'grand_total' => 'decimal:2',
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
     * @return HasMany<SalesInvoiceLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(SalesInvoiceLine::class);
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
        return bcsub((string) $this->grand_total, $this->allocatedAmount(), 2);
    }
}
