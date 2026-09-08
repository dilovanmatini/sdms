<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\ValidatesReportDates;
use App\Enums\ReportType;
use App\Models\SystemSetting;
use App\Services\ReportBuilder;
use App\Support\ArabicPdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PdfAction
{
    use ValidatesReportDates;

    public function __construct(public ReportBuilder $builder) {}

    public function handle(Request $request, string $report): Response
    {
        $viewData = $this->viewData($request, $report);

        return ArabicPdf::fromView('reports.print', $viewData, orientation: 'landscape')
            ->download(sprintf('report-%s-%s.pdf', $report, now()->format('Ymd')));
    }

    /**
     * @return array{report: array<string, mixed>, generated_at: string, app_name: string, forPdf: bool}
     */
    private function viewData(Request $request, string $report): array
    {
        $type = ReportType::from($report);
        [$fromDate, $toDate] = $this->validatedDates($request, $type);

        return [
            'report' => $this->builder->build($type, $fromDate, $toDate),
            'generated_at' => now()->timezone(config('app.timezone'))->format('Y-m-d H:i'),
            'app_name' => SystemSetting::current()->app_name,
            'forPdf' => false,
        ];
    }
}
