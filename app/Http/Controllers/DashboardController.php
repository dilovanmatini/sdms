<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\ShowAction;
use App\Authorization\Ability;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(ShowAction $action): Response
    {
        Gate::authorize(Ability::ViewDashboard->value);

        return $action->handle();
    }
}
