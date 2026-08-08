<?php

namespace App\Http\Controllers;

use App\Authorization\Ability;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize(Ability::ViewInventory->value);

        $search = $request->string('search')->trim()->toString();

        $products = Product::query()
            ->with(['category:id,name', 'unit:id,name,symbol'])
            ->select('products.*')
            ->selectSub(
                InventoryTransaction::query()
                    ->selectRaw('COALESCE(SUM(quantity_in), 0) - COALESCE(SUM(quantity_out), 0)')
                    ->whereColumn('product_id', 'products.id'),
                'available_quantity',
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_ar')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'code' => $product->code,
                'name_ar' => $product->name_ar,
                'unit' => $product->unit?->only(['id', 'name', 'symbol']),
                'category' => $product->category?->only(['id', 'name']),
                'available_quantity' => (string) ($product->available_quantity ?? '0'),
            ]);

        return Inertia::render('inventory/index', [
            'products' => $products,
            'filters' => ['search' => $search],
        ]);
    }
}
