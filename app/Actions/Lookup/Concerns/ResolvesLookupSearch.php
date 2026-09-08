<?php

namespace App\Actions\Lookup\Concerns;

use Illuminate\Http\Request;

trait ResolvesLookupSearch
{
    protected function searchTerm(Request $request): string
    {
        return $request->string('search')->trim()->toString();
    }

    protected function resultLimit(Request $request, int $default = 25): int
    {
        $limit = $request->integer('limit', $default);

        return max(1, min($limit, 50));
    }

    /**
     * @return list<int>
     */
    protected function includeIds(Request $request): array
    {
        $include = $request->input('include', []);

        if (! is_array($include)) {
            $include = [$include];
        }

        return collect($include)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
