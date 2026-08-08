<?php

namespace App\Http\Controllers;

use App\Authorization\Ability;
use App\Services\DashboardMetrics;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(DashboardMetrics $metrics): Response
    {
        Gate::authorize(Ability::ViewDashboard->value);

        return Inertia::render('dashboard', [
            'metrics' => $metrics->build(),
        ]);
    }
}
