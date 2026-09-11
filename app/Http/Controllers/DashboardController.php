<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\ShowAction;
use App\Actions\Dashboard\UpdateNumbersVisibilityAction;
use App\Authorization\Ability;
use App\Http\Requests\UpdateDashboardNumbersVisibilityRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class DashboardController extends Controller
{
    public function show(Request $request, ShowAction $action): Response
    {
        Gate::authorize(Ability::ViewDashboard->value);

        $user = $request->user();
        assert($user instanceof User);

        return $action->handle($user);
    }

    public function updateNumbersVisibility(
        UpdateDashboardNumbersVisibilityRequest $request,
        UpdateNumbersVisibilityAction $action,
    ): RedirectResponse {
        return $action->handle($request);
    }
}
