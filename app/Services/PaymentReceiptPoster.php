<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\OpeningBalance;
use App\Models\PaymentReceipt;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentReceiptPoster
{
    public function post(PaymentReceipt $receipt, User $user): PaymentReceipt
    {
        return DB::transaction(function () use ($receipt, $user): PaymentReceipt {
            /** @var PaymentReceipt $locked */
            $locked = PaymentReceipt::query()
                ->whereKey($receipt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isDraft()) {
                throw new InvalidArgumentException('لا يمكن ترحيل إلا سند القبض المسودة.');
            }

            $totalAmount = number_format((float) $locked->amount, 2, '.', '');

            if (bccomp($totalAmount, '0', 2) !== 1) {
                throw new InvalidArgumentException('إجمالي سند القبض يجب أن يكون أكبر من صفر.');
            }

            $this->allocateToOldestReceivables($locked, $totalAmount);

            CustomerLedgerEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->receipt_date,
                'reference_type' => LedgerReferenceType::Receipt,
                'reference_id' => $locked->id,
                'debit' => 0,
                'credit' => $totalAmount,
            ]);

            AccountsReceivableEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->receipt_date,
                'reference_type' => LedgerReferenceType::Receipt,
                'reference_id' => $locked->id,
                'debit' => 0,
                'credit' => $totalAmount,
            ]);

            $locked->update([
                'status' => DocumentStatus::Posted,
                'posted_at' => now(),
                'posted_by' => $user->id,
            ]);

            return $locked->refresh()->load(['distributor', 'allocations.salesInvoice', 'allocations.openingBalance', 'poster']);
        });
    }

    private function allocateToOldestReceivables(PaymentReceipt $receipt, string $totalAmount): void
    {
        $receipt->allocations()->delete();

        $remainingToApply = $totalAmount;

        $invoices = SalesInvoice::query()
            ->where('distributor_id', $receipt->distributor_id)
            ->where('status', DocumentStatus::Posted)
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $openingBalances = OpeningBalance::query()
            ->where('distributor_id', $receipt->distributor_id)
            ->where('status', DocumentStatus::Posted)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        /** @var Collection<int, array{date: string, sort: int, type: string, remaining: string, invoice_id: int|null, opening_balance_id: int|null}> $receivables */
        $receivables = $invoices
            ->map(fn (SalesInvoice $invoice): array => [
                'date' => $invoice->invoice_date?->toDateString() ?? '',
                'sort' => $invoice->id,
                'type' => 'invoice',
                'remaining' => $invoice->remainingAmount(),
                'invoice_id' => $invoice->id,
                'opening_balance_id' => null,
            ])
            ->concat($openingBalances->map(fn (OpeningBalance $openingBalance): array => [
                'date' => $openingBalance->entry_date?->toDateString() ?? '',
                'sort' => $openingBalance->id,
                'type' => 'opening_balance',
                'remaining' => $openingBalance->remainingAmount(),
                'invoice_id' => null,
                'opening_balance_id' => $openingBalance->id,
            ]))
            ->sort(function (array $left, array $right): int {
                $dateComparison = strcmp($left['date'], $right['date']);

                if ($dateComparison !== 0) {
                    return $dateComparison;
                }

                if ($left['type'] !== $right['type']) {
                    return $left['type'] === 'opening_balance' ? -1 : 1;
                }

                return $left['sort'] <=> $right['sort'];
            })
            ->values();

        foreach ($receivables as $receivable) {
            if (bccomp($remainingToApply, '0', 2) !== 1) {
                break;
            }

            if (bccomp($receivable['remaining'], '0', 2) !== 1) {
                continue;
            }

            $applied = bccomp($remainingToApply, $receivable['remaining'], 2) === 1
                ? $receivable['remaining']
                : $remainingToApply;

            $receipt->allocations()->create([
                'sales_invoice_id' => $receivable['invoice_id'],
                'opening_balance_id' => $receivable['opening_balance_id'],
                'amount' => $applied,
            ]);

            $remainingToApply = bcsub($remainingToApply, $applied, 2);
        }
    }
}
