<?php

namespace App\Actions\PaymentReceipt;

use App\Http\Requests\CancelPaymentReceiptRequest;
use App\Models\PaymentReceipt;
use App\Services\PaymentReceiptCanceller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class CancelAction
{
    public function __construct(private PaymentReceiptCanceller $canceller) {}

    public function handle(CancelPaymentReceiptRequest $request, PaymentReceipt $paymentReceipt): RedirectResponse
    {
        try {
            $this->canceller->cancel($paymentReceipt);
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إلغاء سند القبض بنجاح.']);

        return to_route('payment-receipts.index');
    }
}
