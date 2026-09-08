<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CancelAction;
use App\Actions\Purchase\CreateEditAction;
use App\Actions\Purchase\DestroyAction;
use App\Actions\Purchase\IndexAction;
use App\Actions\Purchase\PostAction;
use App\Actions\Purchase\StoreUpdateAction;
use App\Http\Requests\CancelPurchaseRequest;
use App\Http\Requests\PostPurchaseRequest;
use App\Http\Requests\StoreUpdatePurchaseRequest;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class PurchaseController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', Purchase::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?Purchase $purchase, CreateEditAction $action): Response
    {
        if ($purchase?->exists) {
            $this->authorize('view', $purchase);
        } else {
            $this->authorize('create', Purchase::class);
        }

        return $action->handle($request, $purchase);
    }

    public function storeUpdate(StoreUpdatePurchaseRequest $request, ?Purchase $purchase, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $purchase);
    }

    public function destroy(Purchase $purchase, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $purchase);

        return $action->handle($purchase);
    }

    public function post(PostPurchaseRequest $request, Purchase $purchase, PostAction $action): RedirectResponse
    {
        return $action->handle($request, $purchase);
    }

    public function cancel(CancelPurchaseRequest $request, Purchase $purchase, CancelAction $action): RedirectResponse
    {
        return $action->handle($request, $purchase);
    }
}
