<?php

namespace App\Actions\Report;

use App\Actions\Report\Concerns\ValidatesReportDates;
use App\Enums\ReportType;
use App\Exports\GenericArrayExport;
use App\Services\ReportBuilder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelAction
{
    use ValidatesReportDates;

    public function __construct(public ReportBuilder $builder) {}

    public function handle(Request $request, string $report): BinaryFileResponse
    {
        $type = ReportType::from($report);
        [$fromDate, $toDate] = $this->validatedDates($request, $type);
        $data = $this->builder->build($type, $fromDate, $toDate);

        $headings = array_map(fn (array $column): string => $column['label'], $data['columns']);
        $keys = array_map(fn (array $column): string => $column['key'], $data['columns']);

        $rows = array_map(
            fn (array $row): array => array_map(
                fn (string $key): ?string => $row[$key] ?? null,
                $keys,
            ),
            $data['rows'],
        );

        return Excel::download(
            new GenericArrayExport($headings, $rows, $data['title']),
            sprintf('report-%s-%s.xlsx', $report, now()->format('Ymd')),
        );
    }
}
