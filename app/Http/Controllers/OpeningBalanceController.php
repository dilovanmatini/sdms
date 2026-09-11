<?php

namespace App\Http\Controllers;

use App\Actions\OpeningBalance\CancelAction;
use App\Actions\OpeningBalance\CreateEditAction;
use App\Actions\OpeningBalance\DestroyAction;
use App\Actions\OpeningBalance\IndexAction;
use App\Actions\OpeningBalance\PostAction;
use App\Actions\OpeningBalance\StoreUpdateAction;
use App\Http\Requests\CancelOpeningBalanceRequest;
use App\Http\Requests\PostOpeningBalanceRequest;
use App\Http\Requests\StoreUpdateOpeningBalanceRequest;
use App\Models\OpeningBalance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class OpeningBalanceController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', OpeningBalance::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?OpeningBalance $openingBalance, CreateEditAction $action): Response
    {
        if ($openingBalance?->exists) {
            $this->authorize('view', $openingBalance);
        } else {
            $this->authorize('create', OpeningBalance::class);
        }

        return $action->handle($request, $openingBalance);
    }

    public function storeUpdate(StoreUpdateOpeningBalanceRequest $request, ?OpeningBalance $openingBalance, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $openingBalance);
    }

    public function destroy(OpeningBalance $openingBalance, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $openingBalance);

        return $action->handle($openingBalance);
    }

    public function post(PostOpeningBalanceRequest $request, OpeningBalance $openingBalance, PostAction $action): RedirectResponse
    {
        return $action->handle($request, $openingBalance);
    }

    public function cancel(CancelOpeningBalanceRequest $request, OpeningBalance $openingBalance, CancelAction $action): RedirectResponse
    {
        return $action->handle($request, $openingBalance);
    }
}
