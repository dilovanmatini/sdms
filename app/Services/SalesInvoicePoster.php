<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\InventoryReferenceType;
use App\Enums\LedgerReferenceType;
use App\Models\AccountsReceivableEntry;
use App\Models\CustomerLedgerEntry;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesInvoicePoster
{
    public function post(SalesInvoice $invoice, User $user): SalesInvoice
    {
        return DB::transaction(function () use ($invoice, $user): SalesInvoice {
            /** @var SalesInvoice $locked */
            $locked = SalesInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->with(['lines.product'])
                ->firstOrFail();

            if ($locked->isPosted()) {
                throw new InvalidArgumentException('لا يمكن ترحيل مستند مرحّل مسبقاً.');
            }

            if ($locked->lines->isEmpty()) {
                throw new InvalidArgumentException('لا يمكن ترحيل فاتورة بدون بنود.');
            }

            $quantitiesByProduct = [];

            foreach ($locked->lines as $line) {
                $productId = (int) $line->product_id;
                $quantitiesByProduct[$productId] = bcadd(
                    $quantitiesByProduct[$productId] ?? '0',
                    (string) $line->quantity,
                    3,
                );
            }

            Product::query()
                ->whereIn('id', array_keys($quantitiesByProduct))
                ->lockForUpdate()
                ->get();

            foreach ($quantitiesByProduct as $productId => $requiredQuantity) {
                $available = Product::stockQuantityFor($productId);

                if (bccomp($available, $requiredQuantity, 3) < 0) {
                    $productName = $locked->lines
                        ->firstWhere('product_id', $productId)
                        ?->product
                        ?->name_ar ?? (string) $productId;

                    throw new InvalidArgumentException(
                        "الكمية غير متاحة للمنتج: {$productName}. المتاح: {$available}",
                    );
                }
            }

            foreach ($locked->lines as $line) {
                InventoryTransaction::query()->create([
                    'product_id' => $line->product_id,
                    'transaction_date' => $locked->invoice_date,
                    'reference_type' => InventoryReferenceType::Sale,
                    'reference_id' => $locked->id,
                    'quantity_in' => 0,
                    'quantity_out' => $line->quantity,
                ]);
            }

            CustomerLedgerEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->invoice_date,
                'reference_type' => LedgerReferenceType::Invoice,
                'reference_id' => $locked->id,
                'debit' => $locked->grand_total,
                'credit' => 0,
            ]);

            AccountsReceivableEntry::query()->create([
                'distributor_id' => $locked->distributor_id,
                'entry_date' => $locked->invoice_date,
                'reference_type' => LedgerReferenceType::Invoice,
                'reference_id' => $locked->id,
                'debit' => $locked->grand_total,
                'credit' => 0,
            ]);

            $locked->update([
                'status' => DocumentStatus::Posted,
                'posted_at' => now(),
                'posted_by' => $user->id,
            ]);

            return $locked->refresh()->load(['distributor', 'lines.product', 'poster']);
        });
    }
}
