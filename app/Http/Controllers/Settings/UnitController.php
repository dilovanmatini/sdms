<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Unit\CreateEditAction;
use App\Actions\Unit\DestroyAction;
use App\Actions\Unit\IndexAction;
use App\Actions\Unit\StoreUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', Unit::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?Unit $unit, CreateEditAction $action): Response
    {
        if ($unit?->exists) {
            $this->authorize('update', $unit);
        } else {
            $this->authorize('create', Unit::class);
        }

        return $action->handle($request, $unit);
    }

    public function storeUpdate(StoreUpdateUnitRequest $request, ?Unit $unit, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $unit);
    }

    public function destroy(Unit $unit, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $unit);

        return $action->handle($unit);
    }
}
