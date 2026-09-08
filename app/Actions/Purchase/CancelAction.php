<?php

namespace App\Actions\Purchase;

use App\Http\Requests\CancelPurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseCanceller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class CancelAction
{
    public function __construct(private PurchaseCanceller $canceller) {}

    public function handle(CancelPurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        try {
            $this->canceller->cancel($purchase);
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إلغاء المشترى بنجاح.']);

        return to_route('purchases.index');
    }
}
