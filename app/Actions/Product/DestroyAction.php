<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(Product $product): RedirectResponse
    {
        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المنتج بنجاح.']);

        return to_route('products.index');
    }
}
