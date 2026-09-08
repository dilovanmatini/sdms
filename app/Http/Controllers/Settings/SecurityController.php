<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\Security\EditAction;
use App\Actions\Settings\Security\UpdatePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request, EditAction $action): Response
    {
        return $action->handle($request);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request, UpdatePasswordAction $action): RedirectResponse
    {
        return $action->handle($request);
    }
}
