<?php

namespace App\Actions\SalesInvoice;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use App\Models\SystemSetting;
use App\Support\MoneyDisplay;
use App\Support\QuantityDisplay;
use Illuminate\Http\Response;

class PrintAction
{
    public function handle(SalesInvoice $salesInvoice): Response
    {
        $salesInvoice->load(['lines.product:id,code,name_ar', 'distributor']);

        $settings = SystemSetting::current();

        return response()->view('sales-invoices.print', [
            'invoice' => [
                'number' => $salesInvoice->number,
                'invoice_date' => $salesInvoice->invoice_date?->toDateString(),
                'notes' => $salesInvoice->notes,
                'subtotal' => MoneyDisplay::format($salesInvoice->subtotal, trim: true, thousands: true),
                'discount' => MoneyDisplay::format($salesInvoice->discount, trim: true, thousands: true),
                'grand_total' => MoneyDisplay::format($salesInvoice->grand_total, trim: true, thousands: true),
                'remaining_amount' => MoneyDisplay::format($salesInvoice->remainingAmount(), trim: true, thousands: true),
                'status_label' => $salesInvoice->status->label(),
                'distributor' => [
                    'name' => $salesInvoice->distributor?->name ?? '—',
                    'contact_person' => $salesInvoice->distributor?->contact_person,
                    'phone' => $salesInvoice->distributor?->phone,
                    'address' => $salesInvoice->distributor?->address,
                ],
                'lines' => $salesInvoice->lines->map(fn (SalesInvoiceLine $line): array => [
                    'product_code' => $line->product?->code ?? '—',
                    'product_name' => $line->product?->name_ar ?? '—',
                    'quantity' => QuantityDisplay::format($line->quantity),
                    'unit_price' => MoneyDisplay::format($line->unit_price, trim: true, thousands: true),
                    'line_total' => MoneyDisplay::format($line->line_total, trim: true, thousands: true),
                ])->values()->all(),
            ],
            'app_name' => $settings->app_name,
            'logo_url' => $settings->displayLogoUrl(),
            'invoice_header' => $settings->invoice_header,
            'invoice_footer' => $settings->invoice_footer,
            'back_url' => route('sales-invoices.create-edit', $salesInvoice),
        ]);
    }
}
