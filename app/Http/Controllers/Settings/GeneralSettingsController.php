<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\General\EditAction;
use App\Actions\Settings\General\UpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class GeneralSettingsController extends Controller
{
    public function edit(EditAction $action): Response
    {
        $settings = SystemSetting::current();

        $this->authorize('view', $settings);

        return $action->handle();
    }

    public function update(UpdateGeneralSettingsRequest $request, UpdateAction $action): RedirectResponse
    {
        return $action->handle($request);
    }
}
