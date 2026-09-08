<?php

namespace App\Actions\Product;

use App\Actions\Concerns\FiltersByActiveStatus;
use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use FiltersByActiveStatus;
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $isActive = $this->activeStatusFilter($request);

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
            ->tap(fn ($query) => $this->applyActiveStatusFilter($query, $isActive))
            ->latest('id')
            ->paginate($this->perPage($request, 'products'))
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
            'filters' => [
                'search' => $search,
                'is_active' => $isActive,
            ],
            'active_status_options' => $this->activeStatusOptions(),
        ]);
    }
}
