<?php

namespace App\Http\Controllers;

use App\Actions\Inventory\IndexAction;
use App\Authorization\Ability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        Gate::authorize(Ability::ViewInventory->value);

        return $action->handle($request);
    }
}
