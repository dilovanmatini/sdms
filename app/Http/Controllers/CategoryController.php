<?php

namespace App\Http\Controllers;

use App\Actions\Category\CreateEditAction;
use App\Actions\Category\IndexAction;
use App\Actions\Category\DestroyAction;
use App\Actions\Category\StoreUpdateAction;
use App\Http\Requests\StoreUpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', Category::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?Category $category, CreateEditAction $action): Response
    {
        if ($category?->exists) {
            $this->authorize('update', $category);
        } else {
            $this->authorize('create', Category::class);
        }

        return $action->handle($request, $category);
    }

    public function storeUpdate(StoreUpdateCategoryRequest $request, ?Category $category, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $category);
    }

    public function destroy(Category $category, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $category);

        return $action->handle($category);
    }
}
