<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\OpeningBalance;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OpeningBalanceCanceller
{
    public function cancel(OpeningBalance $openingBalance): OpeningBalance
    {
        return DB::transaction(function () use ($openingBalance): OpeningBalance {
            /** @var OpeningBalance $locked */
            $locked = OpeningBalance::query()
                ->whereKey($openingBalance->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isCancelled()) {
                throw new InvalidArgumentException('لا يمكن إلغاء مستند ملغى مسبقاً.');
            }

            if (! $locked->isPosted()) {
                throw new InvalidArgumentException('لا يمكن إلغاء إلا المبلغ غير المسدد النشط.');
            }

            if (bccomp($locked->allocatedAmount(), '0', 2) > 0) {
                throw new InvalidArgumentException('لا يمكن إلغاء مبلغ غير مسدد مرتبط بسندات قبض نشطة.');
            }

            $amount = number_format((float) $locked->amount, 2, '.', '');

            CustomerLedgerEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->entry_date,
                'reference_type' => LedgerReferenceType::OpeningBalance,
                'reference_id' => $locked->id,
                'debit' => 0,
                'credit' => $amount,
            ]);

            AccountsReceivableEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->entry_date,
                'reference_type' => LedgerReferenceType::OpeningBalance,
                'reference_id' => $locked->id,
                'debit' => 0,
                'credit' => $amount,
            ]);

            $locked->update([
                'status' => DocumentStatus::Cancelled,
            ]);

            return $locked->refresh()->load(['distributor', 'poster']);
        });
    }
}
