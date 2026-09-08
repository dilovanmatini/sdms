<?php

namespace App\Actions\Report\Concerns;

use App\Enums\ReportType;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait ValidatesReportDates
{
    /**
     * @return array{0: Carbon|null, 1: Carbon|null}
     */
    protected function validatedDates(Request $request, ReportType $type): array
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
