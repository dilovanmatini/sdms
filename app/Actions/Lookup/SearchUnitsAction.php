<?php

namespace App\Actions\Lookup;

use App\Actions\Lookup\Concerns\ResolvesLookupSearch;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchUnitsAction
{
    use ResolvesLookupSearch;

    public function handle(Request $request): JsonResponse
    {
        $search = $this->searchTerm($request);
        $limit = $this->resultLimit($request);
        $includeIds = $this->includeIds($request);

        $units = Unit::query()
            ->where(function ($query) use ($includeIds): void {
                $query->where('is_active', true);

                if ($includeIds !== []) {
                    $query->orWhereIn('id', $includeIds);
                }
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'symbol']);

        return response()->json([
            'data' => $units->map(fn (Unit $unit): array => [
                'value' => $unit->id,
                'label' => $unit->symbol
                    ? "{$unit->name} ({$unit->symbol})"
                    : $unit->name,
            ])->values()->all(),
        ]);
    }
}
