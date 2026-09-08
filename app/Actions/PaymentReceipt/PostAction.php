<?php

namespace App\Actions\PaymentReceipt;

use App\Http\Requests\PostPaymentReceiptRequest;
use App\Models\PaymentReceipt;
use App\Services\PaymentReceiptPoster;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class PostAction
{
    public function __construct(private PaymentReceiptPoster $poster) {}

    public function handle(PostPaymentReceiptRequest $request, PaymentReceipt $paymentReceipt): RedirectResponse
    {
        try {
            $this->poster->post($paymentReceipt, $request->user());
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم التأكيد النهائي لسند القبض بنجاح.']);

        return to_route('payment-receipts.index');
    }
}
