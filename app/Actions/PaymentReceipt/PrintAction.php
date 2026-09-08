<?php

namespace App\Actions\PaymentReceipt;

use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptAllocation;
use App\Models\SystemSetting;
use App\Support\MoneyDisplay;
use Illuminate\Http\Response;

class PrintAction
{
    public function handle(PaymentReceipt $paymentReceipt): Response
    {
        $paymentReceipt->load(['allocations.salesInvoice:id,number', 'distributor']);

        $settings = SystemSetting::current();

        $totalAmount = $paymentReceipt->allocations->reduce(
            fn (string $carry, PaymentReceiptAllocation $allocation): string => bcadd($carry, (string) $allocation->amount, 2),
            '0',
        );

        return response()->view('payment-receipts.print', [
            'receipt' => [
                'number' => $paymentReceipt->number,
                'receipt_date' => $paymentReceipt->receipt_date?->toDateString(),
                'notes' => $paymentReceipt->notes,
                'payment_method_label' => $paymentReceipt->payment_method->label(),
                'total_amount' => MoneyDisplay::format($totalAmount, trim: true, thousands: true),
                'status_label' => $paymentReceipt->status->label(),
                'distributor' => [
                    'name' => $paymentReceipt->distributor?->name ?? '—',
                    'contact_person' => $paymentReceipt->distributor?->contact_person,
                    'phone' => $paymentReceipt->distributor?->phone,
                    'address' => $paymentReceipt->distributor?->address,
                ],
                'allocations' => $paymentReceipt->allocations->map(fn (PaymentReceiptAllocation $allocation): array => [
                    'invoice_number' => $allocation->salesInvoice?->number ?? '—',
                    'amount' => MoneyDisplay::format($allocation->amount, trim: true, thousands: true),
                ])->values()->all(),
            ],
            'app_name' => $settings->app_name,
            'logo_url' => $settings->displayLogoUrl(),
            'invoice_header' => $settings->invoice_header,
            'invoice_footer' => $settings->invoice_footer,
            'back_url' => route('payment-receipts.create-edit', $paymentReceipt),
        ]);
    }
}
