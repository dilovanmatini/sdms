<?php

namespace App\Http\Controllers\Settings;

use App\Actions\User\CreateEditAction;
use App\Actions\User\DestroyAction;
use App\Actions\User\IndexAction;
use App\Actions\User\StoreUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', User::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?User $user, CreateEditAction $action): Response
    {
        if ($user?->exists) {
            $this->authorize('update', $user);
        } else {
            $this->authorize('create', User::class);
        }

        return $action->handle($request, $user);
    }

    public function storeUpdate(StoreUpdateUserRequest $request, ?User $user, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $user);
    }

    public function destroy(User $user, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $user);

        return $action->handle($user);
    }
}
