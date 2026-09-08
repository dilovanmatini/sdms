<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\ValidatesReportDates;
use App\Enums\ReportType;
use App\Services\ReportBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowAction
{
    use ValidatesReportDates;

    public function __construct(public ReportBuilder $builder) {}

    public function handle(Request $request, string $report): Response
    {
        $type = ReportType::from($report);
        [$fromDate, $toDate] = $this->validatedDates($request, $type);

        return Inertia::render('reports/show', [
            'report' => $this->builder->build($type, $fromDate, $toDate),
            'filters' => [
                'from_date' => $fromDate?->toDateString(),
                'to_date' => $toDate?->toDateString(),
            ],
            'uses_date_range' => $type->usesDateRange(),
        ]);
    }
}
