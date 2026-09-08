<?php

namespace App\Actions\CustomerStatement;

use App\Http\Requests\ShowCustomerStatementRequest;
use App\Models\Distributor;
use App\Models\SystemSetting;
use App\Services\CustomerStatementBuilder;
use Carbon\Carbon;
use Illuminate\Http\Response;

class PrintAction
{
    public function __construct(public CustomerStatementBuilder $builder) {}

    public function handle(ShowCustomerStatementRequest $request): Response
    {
        return response()->view('statements.print', $this->statementViewData($request));
    }

    /**
     * @return array{statement: array<string, mixed>, generated_at: string, app_name: string, forPdf: bool}
     */
    private function statementViewData(ShowCustomerStatementRequest $request): array
    {
        $data = $request->validated();

        /** @var Distributor $distributor */
        $distributor = Distributor::query()->findOrFail($data['distributor_id']);

        return [
            'statement' => $this->builder->build(
                $distributor,
                isset($data['from_date']) ? Carbon::parse($data['from_date']) : null,
                isset($data['to_date']) ? Carbon::parse($data['to_date']) : null,
            ),
            'generated_at' => now()->timezone(config('app.timezone'))->format('Y-m-d H:i'),
            'app_name' => SystemSetting::current()->app_name,
            'forPdf' => false,
        ];
    }
}
