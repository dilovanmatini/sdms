<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateEditAction;
use App\Actions\Product\DestroyAction;
use App\Actions\Product\IndexAction;
use App\Actions\Product\StoreUpdateAction;
use App\Http\Requests\StoreUpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', Product::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?Product $product, CreateEditAction $action): Response
    {
        if ($product?->exists) {
            $this->authorize('update', $product);
        } else {
            $this->authorize('create', Product::class);
        }

        return $action->handle($request, $product);
    }

    public function storeUpdate(StoreUpdateProductRequest $request, ?Product $product, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $product);
    }

    public function destroy(Product $product, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $product);

        return $action->handle($product);
    }
}
