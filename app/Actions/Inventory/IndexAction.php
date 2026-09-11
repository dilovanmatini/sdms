<?php

namespace App\Actions\Inventory;

use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Support\QuantityDisplay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'stock' => ['nullable', 'string', Rule::in(['all', 'in_stock', 'out_of_stock'])],
        ], [], [
            'search' => 'البحث',
            'category_id' => 'گروپ',
            'stock' => 'حالة المخزون',
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $categoryId = isset($validated['category_id'])
            ? (int) $validated['category_id']
            : null;
        $stock = filled($validated['stock'] ?? null)
            ? (string) $validated['stock']
            : 'in_stock';

        $products = Product::query()
            ->with(['category:id,name', 'unit:id,name,symbol'])
            ->select('products.*')
            ->selectSub($this->availableQuantitySubquery(), 'available_quantity')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when(
                $categoryId !== null,
                fn (Builder $query) => $query->where('category_id', $categoryId),
            )
            ->when($stock === 'in_stock', function (Builder $query): void {
                $query->where($this->availableQuantitySubquery(), '>', 0);
            })
            ->when($stock === 'out_of_stock', function (Builder $query): void {
                $query->where($this->availableQuantitySubquery(), '<=', 0);
            })
            ->orderBy('name_ar')
            ->paginate($this->perPage($request, 'inventory'))
            ->withQueryString()
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'code' => $product->code,
                'name_ar' => $product->name_ar,
                'unit' => $product->unit?->only(['id', 'name', 'symbol']),
                'category' => $product->category?->only(['id', 'name']),
                'available_quantity' => QuantityDisplay::format($product->available_quantity),
            ]);

        $selectedCategory = null;

        if ($categoryId !== null) {
            $category = Category::query()->find($categoryId);

            if ($category !== null) {
                $selectedCategory = [
                    'value' => $category->id,
                    'label' => $category->name,
                ];
            }
        }

        return Inertia::render('inventory/index', [
            'products' => $products,
            'selected_category' => $selectedCategory,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'stock' => $stock,
            ],
            'stock_options' => [
                ['value' => 'all', 'label' => 'كل الكميات'],
                ['value' => 'in_stock', 'label' => 'متوفر'],
                ['value' => 'out_of_stock', 'label' => 'غير متوفر'],
            ],
        ]);
    }

    /**
     * @return Builder<InventoryTransaction>
     */
    private function availableQuantitySubquery(): Builder
    {
        return InventoryTransaction::query()
            ->selectRaw('COALESCE(SUM(quantity_in), 0) - COALESCE(SUM(quantity_out), 0)')
            ->whereColumn('product_id', 'products.id');
    }
}
