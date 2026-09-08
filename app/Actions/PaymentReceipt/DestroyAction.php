<?php

namespace App\Actions\PaymentReceipt;

use App\Models\PaymentReceipt;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(PaymentReceipt $paymentReceipt): RedirectResponse
    {
        $paymentReceipt->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف سند القبض بنجاح.']);

        return to_route('payment-receipts.index');
    }
}
