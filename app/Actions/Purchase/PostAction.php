<?php

namespace App\Actions\Purchase;

use App\Http\Requests\PostPurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchasePoster;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class PostAction
{
    public function __construct(private PurchasePoster $poster) {}

    public function handle(PostPurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        try {
            $this->poster->post($purchase, $request->user());
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم التأكيد النهائي للمشترى بنجاح.']);

        return to_route('purchases.index');
    }
}
