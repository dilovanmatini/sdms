<?php

namespace App\Actions\Settings\General;

use App\Enums\Currency;
use App\Models\SystemSetting;
use Inertia\Inertia;
use Inertia\Response;

class EditAction
{
    public function handle(): Response
    {
        $settings = SystemSetting::current();

        return Inertia::render('settings/general', [
            'settings' => [
                'app_name' => $settings->app_name,
                'currency' => $settings->currency->value,
                'logo_url' => $settings->logo_url,
                'invoice_header' => $settings->invoice_header,
                'invoice_footer' => $settings->invoice_footer,
                'receipt_header' => $settings->receipt_header,
                'receipt_footer' => $settings->receipt_footer,
                'show_dashboard_numbers' => $settings->show_dashboard_numbers,
            ],
            'currency_options' => collect(Currency::cases())
                ->map(fn (Currency $currency): array => [
                    'value' => $currency->value,
                    'label' => $currency->label(),
                ])
                ->values()
                ->all(),
        ]);
    }
}
