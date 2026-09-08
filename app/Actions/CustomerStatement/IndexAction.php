<?php

namespace App\Actions\CustomerStatement;

use App\Models\Distributor;
use App\Services\CustomerStatementBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    public function __construct(public CustomerStatementBuilder $builder) {}

    public function handle(Request $request): Response
    {
        $filters = [
            'distributor_id' => $request->filled('distributor_id')
                ? $request->integer('distributor_id')
                : null,
            'from_date' => $request->string('from_date')->toString() ?: null,
            'to_date' => $request->string('to_date')->toString() ?: null,
        ];

        $statement = null;
        $selectedDistributor = null;

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

            $selectedDistributor = [
                'value' => $distributor->id,
                'label' => $distributor->name,
            ];

            $statement = $this->builder->build(
                $distributor,
                isset($validated['from_date']) ? Carbon::parse($validated['from_date']) : null,
                isset($validated['to_date']) ? Carbon::parse($validated['to_date']) : null,
            );
        }

        return Inertia::render('statements/index', [
            'selected_distributor' => $selectedDistributor,
            'filters' => $filters,
            'statement' => $statement,
        ]);
    }
}
