<?php

namespace App\Actions\Dashboard;

use App\Models\User;
use App\Services\DashboardMetrics;
use App\Support\DashboardNumbersVisibility;
use Inertia\Inertia;
use Inertia\Response;

class ShowAction
{
    public function __construct(public DashboardMetrics $metrics) {}

    public function handle(User $user): Response
    {
        return Inertia::render('dashboard', [
            'metrics' => $this->metrics->build(),
            'show_dashboard_numbers' => DashboardNumbersVisibility::for($user),
        ]);
    }
}
