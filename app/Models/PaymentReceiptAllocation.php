<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\PaymentReceiptAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_receipt_id', 'sales_invoice_id', 'opening_balance_id', 'amount'])]
class PaymentReceiptAllocation extends Model
{
    /** @use HasFactory<PaymentReceiptAllocationFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PaymentReceipt, $this>
     */
    public function paymentReceipt(): BelongsTo
    {
        return $this->belongsTo(PaymentReceipt::class);
    }

    /**
     * @return BelongsTo<SalesInvoice, $this>
     */
    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    /**
     * @return BelongsTo<OpeningBalance, $this>
     */
    public function openingBalance(): BelongsTo
    {
        return $this->belongsTo(OpeningBalance::class);
    }
}
