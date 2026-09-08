<?php

namespace App\Http\Controllers;

use App\Actions\Report\ExcelAction;
use App\Actions\Report\IndexAction;
use App\Actions\Report\PdfAction;
use App\Actions\Report\PrintAction;
use App\Actions\Report\ShowAction;
use App\Authorization\Ability;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ReportController extends Controller
{
    public function index(IndexAction $action): InertiaResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        return $action->handle();
    }

    public function show(Request $request, string $report, ShowAction $action): InertiaResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        return $action->handle($request, $report);
    }

    public function print(Request $request, string $report, PrintAction $action): Response
    {
        Gate::authorize(Ability::ViewReports->value);

        return $action->handle($request, $report);
    }

    public function pdf(Request $request, string $report, PdfAction $action): SymfonyResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        return $action->handle($request, $report);
    }

    public function excel(Request $request, string $report, ExcelAction $action): BinaryFileResponse
    {
        Gate::authorize(Ability::ViewReports->value);

        return $action->handle($request, $report);
    }
}
