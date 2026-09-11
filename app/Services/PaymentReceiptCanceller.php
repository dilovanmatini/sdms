<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentReceiptCanceller
{
    public function cancel(PaymentReceipt $receipt): PaymentReceipt
    {
        return DB::transaction(function () use ($receipt): PaymentReceipt {
            /** @var PaymentReceipt $locked */
            $locked = PaymentReceipt::query()
                ->whereKey($receipt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isCancelled()) {
                throw new InvalidArgumentException('لا يمكن إلغاء مستند ملغى مسبقاً.');
            }

            if (! $locked->isPosted()) {
                throw new InvalidArgumentException('لا يمكن إلغاء إلا سند القبض النشط.');
            }

            $totalAmount = number_format((float) $locked->amount, 2, '.', '');

            CustomerLedgerEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->receipt_date,
                'reference_type' => LedgerReferenceType::Receipt,
                'reference_id' => $locked->id,
                'debit' => $totalAmount,
                'credit' => 0,
            ]);

            AccountsReceivableEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->receipt_date,
                'reference_type' => LedgerReferenceType::Receipt,
                'reference_id' => $locked->id,
                'debit' => $totalAmount,
                'credit' => 0,
            ]);

            $locked->update([
                'status' => DocumentStatus::Cancelled,
            ]);

            return $locked->refresh()->load(['distributor', 'allocations.salesInvoice', 'poster']);
        });
    }
}
