<?php

namespace App\Actions\PaymentReceipt;

use App\Models\PaymentReceipt;
use App\Models\SystemSetting;
use App\Support\ArabicMoneyWords;
use App\Support\MoneyDisplay;
use App\Support\PaymentReceiptBalanceSnapshot;
use Illuminate\Http\Response;

class PrintAction
{
    public function __construct(private PaymentReceiptBalanceSnapshot $snapshot) {}

    public function handle(PaymentReceipt $paymentReceipt): Response
    {
        $paymentReceipt->load('distributor');

        $settings = SystemSetting::current();
        $snapshot = $this->snapshot->forPosted($paymentReceipt);

        return response()->view('payment-receipts.print', [
            'receipt' => [
                'number' => $paymentReceipt->number,
                'receipt_date' => $paymentReceipt->receipt_date?->toDateString(),
                'notes' => $paymentReceipt->notes,
                'payment_method_label' => $paymentReceipt->payment_method->label(),
                'total_amount' => MoneyDisplay::format($snapshot['amount'], trim: true, thousands: true),
                'total_amount_in_words' => ArabicMoneyWords::phrase($snapshot['amount']),
                'balance_before' => MoneyDisplay::format($snapshot['before'], trim: true, thousands: true),
                'balance_after' => MoneyDisplay::format($snapshot['after'], trim: true, thousands: true),
                'status_label' => $paymentReceipt->status->label(),
                'distributor' => [
                    'name' => $paymentReceipt->distributor?->name ?? '—',
                    'contact_person' => $paymentReceipt->distributor?->contact_person,
                    'phone' => $paymentReceipt->distributor?->phone,
                    'address' => $paymentReceipt->distributor?->address,
                ],
            ],
            'app_name' => $settings->app_name,
            'logo_url' => $settings->displayLogoUrl(),
            'invoice_header' => $settings->receipt_header,
            'invoice_footer' => $settings->receipt_footer,
            'back_url' => route('payment-receipts.create-edit', $paymentReceipt),
        ]);
    }
}
