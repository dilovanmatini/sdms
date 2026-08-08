<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $search = $request->string('search')->trim()->toString();

        $products = Product::query()
            ->with(['category:id,name', 'unit:id,name,symbol'])
            ->withExists(['inventoryTransactions', 'purchaseLines', 'salesInvoiceLines'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'name_ar' => $product->name_ar,
                'unit' => $product->unit?->only(['id', 'name', 'symbol']),
                'is_active' => $product->is_active,
                'category' => $product->category?->only(['id', 'name']),
                'can_delete' => ! $product->inventory_transactions_exists
                    && ! $product->purchase_lines_exists
                    && ! $product->sales_invoice_lines_exists,
            ]);

        return Inertia::render('products/index', [
            'products' => $products,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('products/create', [
            'categories' => $this->categoryOptions(),
            'units' => $this->unitOptions(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء المنتج بنجاح.']);

        return to_route('products.index');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('products/edit', [
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'name_ar' => $product->name_ar,
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'notes' => $product->notes,
                'is_active' => $product->is_active,
            ],
            'categories' => $this->categoryOptions(),
            'units' => $this->unitOptions($product->unit_id),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث المنتج بنجاح.']);

        return to_route('products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المنتج بنجاح.']);

        return to_route('products.index');
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, symbol: string|null}>
     */
    private function unitOptions(?int $includeUnitId = null): array
    {
        return Unit::query()
            ->where(function ($query) use ($includeUnitId): void {
                $query->where('is_active', true);

                if ($includeUnitId !== null) {
                    $query->orWhere('id', $includeUnitId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'symbol'])
            ->all();
    }
}
