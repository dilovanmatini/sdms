<?php

namespace App\Actions\Lookup;

use App\Actions\Lookup\Concerns\ResolvesLookupSearch;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuppliersAction
{
    use ResolvesLookupSearch;

    public function handle(Request $request): JsonResponse
    {
        $search = $this->searchTerm($request);
        $limit = $this->resultLimit($request);
        $includeIds = $this->includeIds($request);
        $activeOnly = $request->boolean('active_only', true);

        $suppliers = Supplier::query()
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
            ->get(['id', 'name']);

        return response()->json([
            'data' => $suppliers->map(fn (Supplier $supplier): array => [
                'value' => $supplier->id,
                'label' => $supplier->name,
            ])->values()->all(),
        ]);
    }
}
