<?php

namespace App\Http\Controllers;

use App\Authorization\Ability;
use App\Enums\ReportType;
use App\Exports\GenericArrayExport;
use App\Models\SystemSetting;
use App\Services\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ReportController extends Controller
{
    public function index(): InertiaResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        $reports = array_map(
            fn (ReportType $type): array => [
                'type' => $type->value,
                'title' => $type->label(),
                'description' => $type->description(),
                'uses_date_range' => $type->usesDateRange(),
            ],
            ReportType::cases(),
        );

        $reports[] = [
            'type' => 'customer-statement',
            'title' => 'كشف حساب العميل',
            'description' => 'كشف تفصيلي بحركات الموزع والرصيد الجاري',
            'uses_date_range' => true,
            'external_href' => route('statements.index'),
        ];

        return Inertia::render('reports/index', [
            'reports' => $reports,
        ]);
    }

    public function show(Request $request, string $report, ReportBuilder $builder): InertiaResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        $type = ReportType::from($report);
        [$fromDate, $toDate] = $this->validatedDates($request, $type);

        return Inertia::render('reports/show', [
            'report' => $builder->build($type, $fromDate, $toDate),
            'filters' => [
                'from_date' => $fromDate?->toDateString(),
                'to_date' => $toDate?->toDateString(),
            ],
            'uses_date_range' => $type->usesDateRange(),
        ]);
    }

    public function print(Request $request, string $report, ReportBuilder $builder): Response
    {
        Gate::authorize(Ability::ViewReports->value);

        return response()->view('reports.print', $this->viewData($request, $report, $builder));
    }

    public function pdf(Request $request, string $report, ReportBuilder $builder): SymfonyResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        $viewData = $this->viewData($request, $report, $builder);
        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $pdf = Pdf::loadView('reports.print', [
            ...$viewData,
            'forPdf' => true,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption([
                'fontDir' => $fontDir,
                'fontCache' => $fontDir,
                'isRemoteEnabled' => false,
                'defaultFont' => 'NotoSansArabic',
            ]);

        return $pdf->download(sprintf('report-%s-%s.pdf', $report, now()->format('Ymd')));
    }

    public function excel(Request $request, string $report, ReportBuilder $builder): BinaryFileResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        $type = ReportType::from($report);
        [$fromDate, $toDate] = $this->validatedDates($request, $type);
        $data = $builder->build($type, $fromDate, $toDate);

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

    /**
     * @return array{report: array<string, mixed>, generated_at: string, app_name: string, forPdf: bool}
     */
    private function viewData(Request $request, string $report, ReportBuilder $builder): array
    {
        $type = ReportType::from($report);
        [$fromDate, $toDate] = $this->validatedDates($request, $type);

        return [
            'report' => $builder->build($type, $fromDate, $toDate),
            'generated_at' => now()->timezone(config('app.timezone'))->format('Y-m-d H:i'),
            'app_name' => SystemSetting::current()->app_name,
            'forPdf' => false,
        ];
    }

    /**
     * @return array{0: Carbon|null, 1: Carbon|null}
     */
    private function validatedDates(Request $request, ReportType $type): array
    {
        $rules = [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];

        if ($type->usesDateRange()) {
            $request->validate($rules, [], [
                'from_date' => 'من تاريخ',
                'to_date' => 'إلى تاريخ',
            ]);
        }

        $from = $request->filled('from_date') ? Carbon::parse($request->string('from_date')->toString()) : null;
        $to = $request->filled('to_date') ? Carbon::parse($request->string('to_date')->toString()) : null;

        return [$from, $to];
    }
}
