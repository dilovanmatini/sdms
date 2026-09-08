<?php

namespace App\Actions\Settings\General;

use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UpdateAction
{
    public function handle(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $settings = SystemSetting::current();

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
