<?php

namespace App\Http\Controllers;

use App\Authorization\Ability;
use App\Http\Requests\ShowCustomerStatementRequest;
use App\Models\Distributor;
use App\Models\SystemSetting;
use App\Services\CustomerStatementBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CustomerStatementController extends Controller
{
    public function index(
        Request $request,
        CustomerStatementBuilder $builder,
    ): InertiaResponse {
        Gate::authorize(Ability::ViewStatements->value);

        $filters = [
            'distributor_id' => $request->filled('distributor_id')
                ? $request->integer('distributor_id')
                : null,
            'from_date' => $request->string('from_date')->toString() ?: null,
            'to_date' => $request->string('to_date')->toString() ?: null,
        ];

        $statement = null;

        if ($filters['distributor_id'] !== null) {
            $validated = $request->validate([
                'distributor_id' => ['required', 'integer', 'exists:distributors,id'],
                'from_date' => ['nullable', 'date'],
                'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            ], [], [
                'distributor_id' => 'الموزع',
                'from_date' => 'من تاريخ',
                'to_date' => 'إلى تاريخ',
            ]);

            /** @var Distributor $distributor */
            $distributor = Distributor::query()->findOrFail($validated['distributor_id']);

            $statement = $builder->build(
                $distributor,
                isset($validated['from_date']) ? Carbon::parse($validated['from_date']) : null,
                isset($validated['to_date']) ? Carbon::parse($validated['to_date']) : null,
            );
        }

        return Inertia::render('statements/index', [
            'distributors' => $this->distributorOptions(),
            'filters' => $filters,
            'statement' => $statement,
        ]);
    }

    public function print(
        ShowCustomerStatementRequest $request,
        CustomerStatementBuilder $builder,
    ): Response {
        return response()->view('statements.print', $this->statementViewData($request, $builder));
    }

    public function pdf(
        ShowCustomerStatementRequest $request,
        CustomerStatementBuilder $builder,
    ): SymfonyResponse {
        $viewData = $this->statementViewData($request, $builder);

        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $pdf = Pdf::loadView('statements.print', [
            ...$viewData,
            'forPdf' => true,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'fontDir' => $fontDir,
                'fontCache' => $fontDir,
                'isRemoteEnabled' => false,
                'defaultFont' => 'NotoSansArabic',
            ]);

        $filename = sprintf(
            'statement-%s-%s.pdf',
            $viewData['statement']['distributor']['id'],
            now()->format('Ymd'),
        );

        return $pdf->download($filename);
    }

    /**
     * @return array{statement: array<string, mixed>, generated_at: string, app_name: string, forPdf: bool}
     */
    private function statementViewData(
        ShowCustomerStatementRequest $request,
        CustomerStatementBuilder $builder,
    ): array {
        $data = $request->validated();

        /** @var Distributor $distributor */
        $distributor = Distributor::query()->findOrFail($data['distributor_id']);

        return [
            'statement' => $builder->build(
                $distributor,
                isset($data['from_date']) ? Carbon::parse($data['from_date']) : null,
                isset($data['to_date']) ? Carbon::parse($data['to_date']) : null,
            ),
            'generated_at' => now()->timezone(config('app.timezone'))->format('Y-m-d H:i'),
            'app_name' => SystemSetting::current()->app_name,
            'forPdf' => false,
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function distributorOptions(): array
    {
        return Distributor::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }
}
