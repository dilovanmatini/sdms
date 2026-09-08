<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function handle(Request $request, ?Product $product): RedirectResponse
    {
        if ($product?->exists) {
            $product->update($request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث المنتج بنجاح.']);

            return to_route('products.create-edit', $product);
        }

        $product = Product::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء المنتج بنجاح.']);

        return to_route('products.create-edit', $product);
    }
}
