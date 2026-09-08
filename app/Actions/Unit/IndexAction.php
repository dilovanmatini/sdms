<?php

namespace App\Actions\Unit;

use App\Actions\Concerns\FiltersByActiveStatus;
use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use FiltersByActiveStatus;
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $isActive = $this->activeStatusFilter($request);

        $units = Unit::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->tap(fn ($query) => $this->applyActiveStatusFilter($query, $isActive))
            ->orderBy('name')
            ->paginate($this->perPage($request, 'units'))
            ->withQueryString()
            ->through(fn (Unit $unit): array => [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'is_active' => $unit->is_active,
                'products_count' => $unit->products_count,
                'can_delete' => $unit->products_count === 0,
            ]);

        return Inertia::render('settings/units/index', [
            'units' => $units,
            'filters' => [
                'search' => $search,
                'is_active' => $isActive,
            ],
            'active_status_options' => $this->activeStatusOptions(),
        ]);
    }
}
