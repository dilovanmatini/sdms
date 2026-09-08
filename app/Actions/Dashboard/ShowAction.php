<?php

namespace App\Actions\Dashboard;

use App\Services\DashboardMetrics;
use Inertia\Inertia;
use Inertia\Response;

class ShowAction
{
    public function __construct(public DashboardMetrics $metrics) {}

    public function handle(): Response
    {
        return Inertia::render('dashboard', [
            'metrics' => $this->metrics->build(),
        ]);
    }
}
