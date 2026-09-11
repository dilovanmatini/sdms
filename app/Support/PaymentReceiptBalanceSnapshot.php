<?php

namespace App\Support;

use App\Enums\LedgerReferenceType;
use App\Models\CustomerLedgerEntry;
use App\Models\Distributor;
use App\Models\PaymentReceipt;

final class PaymentReceiptBalanceSnapshot
{
    /**
     * Historical distributor balance around a posted receipt.
     *
     * @return array{before: string, amount: string, after: string}
     */
    public function forPosted(PaymentReceipt $receipt): array
    {
        $amount = $this->amount($receipt);

        $credit = CustomerLedgerEntry::query()
            ->where('distributor_id', $receipt->distributor_id)
            ->where('reference_type', LedgerReferenceType::Receipt)
            ->where('reference_id', $receipt->id)
            ->where('credit', '>', 0)
            ->orderBy('id')
            ->first();

        if ($credit === null) {
            return $this->fromCurrentBalance($receipt->distributor_id, $amount);
        }

        $before = CustomerLedgerEntry::query()
            ->where('distributor_id', $receipt->distributor_id)
            ->where(function ($query) use ($credit): void {
                $query->whereDate('entry_date', '<', $credit->entry_date)
                    ->orWhere(function ($query) use ($credit): void {
                        $query->whereDate('entry_date', $credit->entry_date)
                            ->where('id', '<', $credit->id);
                    });
            })
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        $before = number_format((float) ($before ?? 0), 2, '.', '');

        return [
            'before' => $before,
            'amount' => $amount,
            'after' => bcsub($before, $amount, 2),
        ];
    }

    /**
     * Current outstanding vs a draft receipt amount.
     *
     * @return array{before: string, amount: string, after: string}
     */
    public function preview(int $distributorId, mixed $amount): array
    {
        return $this->fromCurrentBalance(
            $distributorId,
            number_format((float) $amount, 2, '.', ''),
        );
    }

    /**
     * @return array{before: string, amount: string, after: string}
     */
    private function fromCurrentBalance(int $distributorId, string $amount): array
    {
        $before = number_format((float) Distributor::balanceFor($distributorId), 2, '.', '');

        return [
            'before' => $before,
            'amount' => $amount,
            'after' => bcsub($before, $amount, 2),
        ];
    }

    private function amount(PaymentReceipt $receipt): string
    {
        return number_format((float) $receipt->amount, 2, '.', '');
    }
}
