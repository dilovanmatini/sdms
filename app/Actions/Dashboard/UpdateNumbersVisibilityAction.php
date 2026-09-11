<?php

namespace App\Actions\Dashboard;

use App\Http\Requests\UpdateDashboardNumbersVisibilityRequest;
use Illuminate\Http\RedirectResponse;

class UpdateNumbersVisibilityAction
{
    public function handle(UpdateDashboardNumbersVisibilityRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->show_dashboard_numbers = $request->boolean('show_dashboard_numbers');
        $user->save();

        return back();
    }
}
