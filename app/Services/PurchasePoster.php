<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\InventoryReferenceType;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchasePoster
{
    public function post(Purchase $purchase, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase, $user): Purchase {
            /** @var Purchase $locked */
            $locked = Purchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->with('lines')
                ->firstOrFail();

            if (! $locked->isDraft()) {
                throw new InvalidArgumentException('لا يمكن ترحيل إلا مسودة المشترى.');
            }

            if ($locked->lines->isEmpty()) {
                throw new InvalidArgumentException('لا يمكن ترحيل مشترى بدون بنود.');
            }

            foreach ($locked->lines as $line) {
                InventoryTransaction::query()->create([
                    'product_id' => $line->product_id,
                    'transaction_date' => $locked->purchase_date,
                    'reference_type' => InventoryReferenceType::Purchase,
                    'reference_id' => $locked->id,
                    'quantity_in' => $line->quantity,
                    'quantity_out' => 0,
                ]);
            }

            $locked->update([
                'status' => DocumentStatus::Posted,
                'posted_at' => now(),
                'posted_by' => $user->id,
            ]);

            return $locked->refresh()->load(['supplier', 'lines.product', 'poster']);
        });
    }
}
