<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\InventoryReferenceType;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseCanceller
{
    public function cancel(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase): Purchase {
            /** @var Purchase $locked */
            $locked = Purchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->with('lines')
                ->firstOrFail();

            if ($locked->isCancelled()) {
                throw new InvalidArgumentException('لا يمكن إلغاء مستند ملغى مسبقاً.');
            }

            if (! $locked->isPosted()) {
                throw new InvalidArgumentException('لا يمكن إلغاء إلا المشترى النشط.');
            }

            foreach ($locked->lines as $line) {
                InventoryTransaction::query()->create([
                    'product_id' => $line->product_id,
                    'transaction_date' => $locked->purchase_date,
                    'reference_type' => InventoryReferenceType::Purchase,
                    'reference_id' => $locked->id,
                    'quantity_in' => 0,
                    'quantity_out' => $line->quantity,
                ]);
            }

            $locked->update([
                'status' => DocumentStatus::Cancelled,
            ]);

            return $locked->refresh()->load(['supplier', 'lines.product', 'poster']);
        });
    }
}
