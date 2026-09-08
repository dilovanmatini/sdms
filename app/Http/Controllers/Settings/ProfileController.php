<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\Profile\DestroyAction;
use App\Actions\Settings\Profile\EditAction;
use App\Actions\Settings\Profile\UpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request, EditAction $action): Response
    {
        return $action->handle($request);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, UpdateAction $action): RedirectResponse
    {
        return $action->handle($request);
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request, DestroyAction $action): RedirectResponse
    {
        return $action->handle($request);
    }
}
