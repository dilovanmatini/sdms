<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\PaymentReceipt;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Support\MoneyDisplay;
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
                ->with(['allocations.salesInvoice'])
                ->firstOrFail();

            if (! $locked->isDraft()) {
                throw new InvalidArgumentException('لا يمكن ترحيل إلا سند القبض المسودة.');
            }

            if ($locked->allocations->isEmpty()) {
                throw new InvalidArgumentException('لا يمكن ترحيل سند قبض بدون توزيعات.');
            }

            $invoiceIds = $locked->allocations->pluck('sales_invoice_id')->all();

            SalesInvoice::query()
                ->whereIn('id', $invoiceIds)
                ->lockForUpdate()
                ->get();

            $totalAmount = '0';

            foreach ($locked->allocations as $allocation) {
                $invoice = $allocation->salesInvoice;

                if ($invoice === null || ! $invoice->isPosted()) {
                    throw new InvalidArgumentException('يمكن التوزيع فقط على فواتير مبيعات نشطة.');
                }

                if ((int) $invoice->distributor_id !== (int) $locked->distributor_id) {
                    throw new InvalidArgumentException('الفاتورة لا تتبع نفس الموزع.');
                }

                $remaining = $invoice->remainingAmount();

                if (bccomp((string) $allocation->amount, $remaining, 2) === 1) {
                    throw new InvalidArgumentException(
                        'مبلغ التوزيع يتجاوز المتبقي للفاتورة '.$invoice->number.'. المتبقي: '.MoneyDisplay::withSymbol($remaining),
                    );
                }

                $totalAmount = bcadd($totalAmount, (string) $allocation->amount, 2);
            }

            if (bccomp($totalAmount, '0', 2) !== 1) {
                throw new InvalidArgumentException('إجمالي سند القبض يجب أن يكون أكبر من صفر.');
            }

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

            return $locked->refresh()->load(['distributor', 'allocations.salesInvoice', 'poster']);
        });
    }
}
