<?php

namespace App\Actions\Lookup;

use App\Actions\Lookup\Concerns\ResolvesLookupSearch;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchCategoriesAction
{
    use ResolvesLookupSearch;

    public function handle(Request $request): JsonResponse
    {
        $search = $this->searchTerm($request);
        $limit = $this->resultLimit($request);
        $includeIds = $this->includeIds($request);

        $categories = Category::query()
            ->where(function ($query) use ($includeIds): void {
                $query->where('is_active', true);

                if ($includeIds !== []) {
                    $query->orWhereIn('id', $includeIds);
                }
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $categories->map(fn (Category $category): array => [
                'value' => $category->id,
                'label' => $category->name,
            ])->values()->all(),
        ]);
    }
}
