<?php

namespace App\Http\Controllers;

use App\Actions\Distributor\CreateEditAction;
use App\Actions\Distributor\DestroyAction;
use App\Actions\Distributor\IndexAction;
use App\Actions\Distributor\StoreUpdateAction;
use App\Http\Requests\StoreUpdateDistributorRequest;
use App\Models\Distributor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class DistributorController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', Distributor::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?Distributor $distributor, CreateEditAction $action): Response
    {
        if ($distributor?->exists) {
            $this->authorize('update', $distributor);
        } else {
            $this->authorize('create', Distributor::class);
        }

        return $action->handle($request, $distributor);
    }

    public function storeUpdate(StoreUpdateDistributorRequest $request, ?Distributor $distributor, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $distributor);
    }

    public function destroy(Distributor $distributor, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $distributor);

        return $action->handle($distributor);
    }
}
