<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GeneralSettingsController extends Controller
{
    public function edit(): Response
    {
        $settings = SystemSetting::current();

        $this->authorize('view', $settings);

        return Inertia::render('settings/general', [
            'settings' => [
                'app_name' => $settings->app_name,
                'logo_url' => $settings->logo_url,
                'invoice_header' => $settings->invoice_header,
                'invoice_footer' => $settings->invoice_footer,
                'receipt_header' => $settings->receipt_header,
                'receipt_footer' => $settings->receipt_footer,
            ],
        ]);
    }

    public function update(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $settings = SystemSetting::current();

        $this->authorize('update', $settings);

        $data = $request->safe()->except(['logo', 'remove_logo']);

        if ($request->boolean('remove_logo') || $request->hasFile('logo')) {
            $this->deleteLogo($settings);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            /** @var UploadedFile $logo */
            $logo = $request->file('logo');
            $data['logo_path'] = $logo->store('settings', 'public');
        }

        $settings->update($data);

        Config::set('app.name', $settings->app_name);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حفظ الإعدادات العامة بنجاح.']);

        return to_route('settings.general.edit');
    }

    private function deleteLogo(SystemSetting $settings): void
    {
        if ($settings->logo_path === null || $settings->logo_path === '') {
            return;
        }

        Storage::disk('public')->delete($settings->logo_path);
    }
}
