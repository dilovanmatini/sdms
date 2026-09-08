<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\InventoryReferenceType;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\InventoryTransaction;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesInvoiceCanceller
{
    public function cancel(SalesInvoice $invoice): SalesInvoice
    {
        return DB::transaction(function () use ($invoice): SalesInvoice {
            /** @var SalesInvoice $locked */
            $locked = SalesInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->with('lines')
                ->firstOrFail();

            if ($locked->isCancelled()) {
                throw new InvalidArgumentException('لا يمكن إلغاء مستند ملغى مسبقاً.');
            }

            if (! $locked->isPosted()) {
                throw new InvalidArgumentException('لا يمكن إلغاء إلا الفاتورة النشطة.');
            }

            if (bccomp($locked->allocatedAmount(), '0', 2) > 0) {
                throw new InvalidArgumentException('لا يمكن إلغاء فاتورة مرتبطة بسندات قبض نشطة.');
            }

            foreach ($locked->lines as $line) {
                InventoryTransaction::query()->create([
                    'product_id' => $line->product_id,
                    'transaction_date' => $locked->invoice_date,
                    'reference_type' => InventoryReferenceType::Sale,
                    'reference_id' => $locked->id,
                    'quantity_in' => $line->quantity,
                    'quantity_out' => 0,
                ]);
            }

            CustomerLedgerEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->invoice_date,
                'reference_type' => LedgerReferenceType::Invoice,
                'reference_id' => $locked->id,
                'debit' => 0,
                'credit' => $locked->grand_total,
            ]);

            AccountsReceivableEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->invoice_date,
                'reference_type' => LedgerReferenceType::Invoice,
                'reference_id' => $locked->id,
                'debit' => 0,
                'credit' => $locked->grand_total,
            ]);

            $locked->update([
                'status' => DocumentStatus::Cancelled,
            ]);

            return $locked->refresh()->load(['distributor', 'lines.product', 'poster']);
        });
    }
}
