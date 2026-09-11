<?php

namespace App\Actions\Lookup;

use App\Actions\Lookup\Concerns\ResolvesLookupSearch;
use App\Models\CustomerLedgerEntry;
use App\Models\Distributor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchDistributorsAction
{
    use ResolvesLookupSearch;

    public function handle(Request $request): JsonResponse
    {
        $search = $this->searchTerm($request);
        $limit = $this->resultLimit($request);
        $includeIds = $this->includeIds($request);
        $activeOnly = $request->boolean('active_only', true);

        $distributors = Distributor::query()
            ->when($activeOnly, function ($query) use ($includeIds): void {
                $query->where(function ($query) use ($includeIds): void {
                    $query->where('is_active', true);

                    if ($includeIds !== []) {
                        $query->orWhereIn('id', $includeIds);
                    }
                });
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'contact_person', 'phone']);

        $balances = $distributors->isEmpty()
            ? collect()
            : CustomerLedgerEntry::query()
                ->whereIn('distributor_id', $distributors->modelKeys())
                ->select('distributor_id')
                ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
                ->groupBy('distributor_id')
                ->pluck('balance', 'distributor_id');

        return response()->json([
            'data' => $distributors->map(fn (Distributor $distributor): array => [
                'value' => $distributor->id,
                'label' => $distributor->name,
                'meta' => [
                    'contact_person' => $distributor->contact_person,
                    'phone' => $distributor->phone,
                    'balance' => number_format((float) ($balances[$distributor->id] ?? 0), 2, '.', ''),
                ],
            ])->values()->all(),
        ]);
    }
}
