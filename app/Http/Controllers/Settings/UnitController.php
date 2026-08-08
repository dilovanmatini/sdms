<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Unit::class);

        $search = $request->string('search')->trim()->toString();

        $units = Unit::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
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
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Unit::class);

        return Inertia::render('settings/units/create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء وحدة القياس بنجاح.']);

        return to_route('units.index');
    }

    public function edit(Unit $unit): Response
    {
        $this->authorize('update', $unit);

        return Inertia::render('settings/units/edit', [
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'is_active' => $unit->is_active,
            ],
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث وحدة القياس بنجاح.']);

        return to_route('units.index');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $this->authorize('delete', $unit);

        $unit->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف وحدة القياس بنجاح.']);

        return to_route('units.index');
    }
}
