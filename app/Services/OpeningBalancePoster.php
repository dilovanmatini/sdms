<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\OpeningBalance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OpeningBalancePoster
{
    public function post(OpeningBalance $openingBalance, User $user): OpeningBalance
    {
        return DB::transaction(function () use ($openingBalance, $user): OpeningBalance {
            /** @var OpeningBalance $locked */
            $locked = OpeningBalance::query()
                ->whereKey($openingBalance->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isDraft()) {
                throw new InvalidArgumentException('لا يمكن ترحيل إلا مسودة المبلغ غير المسدد.');
            }

            $amount = number_format((float) $locked->amount, 2, '.', '');

            if (bccomp($amount, '0', 2) !== 1) {
                throw new InvalidArgumentException('المبلغ غير المسدد يجب أن يكون أكبر من صفر.');
            }

            CustomerLedgerEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->entry_date,
                'reference_type' => LedgerReferenceType::OpeningBalance,
                'reference_id' => $locked->id,
                'debit' => $amount,
                'credit' => 0,
            ]);

            AccountsReceivableEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->entry_date,
                'reference_type' => LedgerReferenceType::OpeningBalance,
                'reference_id' => $locked->id,
                'debit' => $amount,
                'credit' => 0,
            ]);

            $locked->update([
                'status' => DocumentStatus::Posted,
                'posted_at' => now(),
                'posted_by' => $user->id,
            ]);

            return $locked->refresh()->load(['distributor', 'poster']);
        });
    }
}
