<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Requests\PostPurchaseRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\Supplier;
use App\Services\DocumentNumberGenerator;
use App\Services\PurchasePoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class PurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Purchase::class);

        $search = $request->string('search')->trim()->toString();

        $purchases = Purchase::query()
            ->with(['supplier:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Purchase $purchase): array => [
                'id' => $purchase->id,
                'number' => $purchase->number,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'supplier' => $purchase->supplier?->only(['id', 'name']),
                'status' => $purchase->status->value,
                'status_label' => $purchase->status->label(),
                'is_posted' => $purchase->isPosted(),
                'can_edit' => ! $purchase->isPosted(),
                'can_delete' => ! $purchase->isPosted(),
            ]);

        return Inertia::render('purchases/index', [
            'purchases' => $purchases,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Purchase::class);

        return Inertia::render('purchases/create', [
            'suppliers' => $this->supplierOptions(),
            'products' => $this->productOptions(),
        ]);
    }

    public function store(
        StorePurchaseRequest $request,
        DocumentNumberGenerator $numbers,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $numbers): void {
            $data = $request->validated();

            $purchase = Purchase::query()->create([
                'number' => $numbers->generate(DocumentType::Purchase),
                'purchase_date' => $data['purchase_date'],
                'supplier_id' => $data['supplier_id'],
                'notes' => $data['notes'] ?? null,
                'status' => DocumentStatus::Draft,
            ]);

            $this->syncLines($purchase, $data['lines']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء المشترى بنجاح.']);

        return to_route('purchases.index');
    }

    public function edit(Purchase $purchase): Response
    {
        $this->authorize('view', $purchase);

        $purchase->load(['lines.product:id,code,name_ar', 'supplier:id,name']);

        $lineProductIds = $purchase->lines->pluck('product_id')->all();

        return Inertia::render('purchases/edit', [
            'purchase' => [
                'id' => $purchase->id,
                'number' => $purchase->number,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'supplier_id' => $purchase->supplier_id,
                'notes' => $purchase->notes,
                'status' => $purchase->status->value,
                'status_label' => $purchase->status->label(),
                'is_posted' => $purchase->isPosted(),
                'posted_at' => $purchase->posted_at?->toIso8601String(),
                'lines' => $purchase->lines->map(fn (PurchaseLine $line): array => [
                    'product_id' => $line->product_id,
                    'quantity' => (string) $line->quantity,
                    'product' => $line->product?->only(['id', 'code', 'name_ar']),
                ])->values()->all(),
            ],
            'suppliers' => $this->supplierOptions($purchase->supplier_id),
            'products' => $this->productOptions($lineProductIds),
            'can_edit' => ! $purchase->isPosted(),
            'can_post' => ! $purchase->isPosted() && $purchase->lines->isNotEmpty(),
        ]);
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase): void {
            $data = $request->validated();

            $purchase->update([
                'purchase_date' => $data['purchase_date'],
                'supplier_id' => $data['supplier_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($purchase, $data['lines']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث المشترى بنجاح.']);

        return to_route('purchases.index');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $this->authorize('delete', $purchase);

        $purchase->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المشترى بنجاح.']);

        return to_route('purchases.index');
    }

    public function post(
        PostPurchaseRequest $request,
        Purchase $purchase,
        PurchasePoster $poster,
    ): RedirectResponse {
        try {
            $poster->post($purchase, $request->user());
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم ترحيل المشترى بنجاح.']);

        return to_route('purchases.index');
    }

    /**
     * @param  list<array{product_id: int, quantity: numeric-string|float|int}>  $lines
     */
    private function syncLines(Purchase $purchase, array $lines): void
    {
        $purchase->lines()->delete();

        foreach ($lines as $line) {
            $purchase->lines()->create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
            ]);
        }
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function supplierOptions(?int $includeSupplierId = null): array
    {
        return Supplier::query()
            ->where(function ($query) use ($includeSupplierId): void {
                $query->where('is_active', true);

                if ($includeSupplierId !== null) {
                    $query->orWhere('id', $includeSupplierId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    /**
     * @param  list<int>  $includeProductIds
     * @return list<array{id: int, code: string, name_ar: string}>
     */
    private function productOptions(array $includeProductIds = []): array
    {
        return Product::query()
            ->where(function ($query) use ($includeProductIds): void {
                $query->where('is_active', true);

                if ($includeProductIds !== []) {
                    $query->orWhereIn('id', $includeProductIds);
                }
            })
            ->orderBy('name_ar')
            ->get(['id', 'code', 'name_ar'])
            ->all();
    }
}
