<?php

namespace App\Http\Controllers;

use App\Actions\Supplier\CreateEditAction;
use App\Actions\Supplier\DestroyAction;
use App\Actions\Supplier\IndexAction;
use App\Actions\Supplier\StoreUpdateAction;
use App\Http\Requests\StoreUpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', Supplier::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?Supplier $supplier, CreateEditAction $action): Response
    {
        if ($supplier?->exists) {
            $this->authorize('update', $supplier);
        } else {
            $this->authorize('create', Supplier::class);
        }

        return $action->handle($request, $supplier);
    }

    public function storeUpdate(StoreUpdateSupplierRequest $request, ?Supplier $supplier, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $supplier);
    }

    public function destroy(Supplier $supplier, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        return $action->handle($supplier);
    }
}
