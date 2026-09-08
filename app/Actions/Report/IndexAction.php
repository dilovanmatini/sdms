<?php

namespace App\Actions\Report;

use App\Enums\ReportType;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    public function handle(): Response
    {
        $reports = array_map(
            fn (ReportType $type): array => [
                'type' => $type->value,
                'title' => $type->label(),
                'description' => $type->description(),
                'uses_date_range' => $type->usesDateRange(),
            ],
            ReportType::cases(),
        );

        return Inertia::render('reports/index', [
            'reports' => $reports,
        ]);
    }
}
