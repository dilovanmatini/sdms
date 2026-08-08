<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Requests\PostSalesInvoiceRequest;
use App\Http\Requests\StoreSalesInvoiceRequest;
use App\Http\Requests\UpdateSalesInvoiceRequest;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use App\Services\DocumentNumberGenerator;
use App\Services\SalesInvoicePoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class SalesInvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SalesInvoice::class);

        $search = $request->string('search')->trim()->toString();

        $invoices = SalesInvoice::query()
            ->with(['distributor:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('distributor', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (SalesInvoice $invoice): array => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'distributor' => $invoice->distributor?->only(['id', 'name']),
                'grand_total' => (string) $invoice->grand_total,
                'status' => $invoice->status->value,
                'status_label' => $invoice->status->label(),
                'is_posted' => $invoice->isPosted(),
                'can_edit' => ! $invoice->isPosted(),
                'can_delete' => ! $invoice->isPosted(),
            ]);

        return Inertia::render('sales-invoices/index', [
            'invoices' => $invoices,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SalesInvoice::class);

        return Inertia::render('sales-invoices/create', [
            'distributors' => $this->distributorOptions(),
            'products' => $this->productOptions(),
        ]);
    }

    public function store(
        StoreSalesInvoiceRequest $request,
        DocumentNumberGenerator $numbers,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $numbers): void {
            $data = $request->validated();
            $totals = $this->calculateTotals($data['lines'], $data['discount']);

            $invoice = SalesInvoice::query()->create([
                'number' => $numbers->generate(DocumentType::Invoice),
                'invoice_date' => $data['invoice_date'],
                'distributor_id' => $data['distributor_id'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'grand_total' => $totals['grand_total'],
                'status' => DocumentStatus::Draft,
            ]);

            $this->syncLines($invoice, $data['lines']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء فاتورة المبيعات بنجاح.']);

        return to_route('sales-invoices.index');
    }

    public function edit(SalesInvoice $salesInvoice): Response
    {
        $this->authorize('view', $salesInvoice);

        $salesInvoice->load(['lines.product:id,code,name_ar', 'distributor:id,name']);

        $lineProductIds = $salesInvoice->lines->pluck('product_id')->all();

        return Inertia::render('sales-invoices/edit', [
            'invoice' => [
                'id' => $salesInvoice->id,
                'number' => $salesInvoice->number,
                'invoice_date' => $salesInvoice->invoice_date?->toDateString(),
                'distributor_id' => $salesInvoice->distributor_id,
                'notes' => $salesInvoice->notes,
                'subtotal' => (string) $salesInvoice->subtotal,
                'discount' => (string) $salesInvoice->discount,
                'grand_total' => (string) $salesInvoice->grand_total,
                'status' => $salesInvoice->status->value,
                'status_label' => $salesInvoice->status->label(),
                'is_posted' => $salesInvoice->isPosted(),
                'posted_at' => $salesInvoice->posted_at?->toIso8601String(),
                'lines' => $salesInvoice->lines->map(fn (SalesInvoiceLine $line): array => [
                    'product_id' => $line->product_id,
                    'quantity' => (string) $line->quantity,
                    'unit_price' => (string) $line->unit_price,
                    'line_total' => (string) $line->line_total,
                    'product' => $line->product?->only(['id', 'code', 'name_ar']),
                ])->values()->all(),
            ],
            'distributors' => $this->distributorOptions($salesInvoice->distributor_id),
            'products' => $this->productOptions($lineProductIds),
            'can_edit' => ! $salesInvoice->isPosted(),
            'can_post' => ! $salesInvoice->isPosted() && $salesInvoice->lines->isNotEmpty(),
        ]);
    }

    public function update(
        UpdateSalesInvoiceRequest $request,
        SalesInvoice $salesInvoice,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $salesInvoice): void {
            $data = $request->validated();
            $totals = $this->calculateTotals($data['lines'], $data['discount']);

            $salesInvoice->update([
                'invoice_date' => $data['invoice_date'],
                'distributor_id' => $data['distributor_id'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'grand_total' => $totals['grand_total'],
            ]);

            $this->syncLines($salesInvoice, $data['lines']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث فاتورة المبيعات بنجاح.']);

        return to_route('sales-invoices.index');
    }

    public function destroy(SalesInvoice $salesInvoice): RedirectResponse
    {
        $this->authorize('delete', $salesInvoice);

        $salesInvoice->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف فاتورة المبيعات بنجاح.']);

        return to_route('sales-invoices.index');
    }

    public function post(
        PostSalesInvoiceRequest $request,
        SalesInvoice $salesInvoice,
        SalesInvoicePoster $poster,
    ): RedirectResponse {
        try {
            $poster->post($salesInvoice, $request->user());
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم ترحيل فاتورة المبيعات بنجاح.']);

        return to_route('sales-invoices.index');
    }

    /**
     * @param  list<array{product_id: int, quantity: numeric-string|float|int, unit_price: numeric-string|float|int}>  $lines
     * @return array{subtotal: string, discount: string, grand_total: string}
     */
    private function calculateTotals(array $lines, mixed $discount): array
    {
        $subtotal = '0';

        foreach ($lines as $line) {
            $lineTotal = bcmul((string) $line['quantity'], (string) $line['unit_price'], 2);
            $subtotal = bcadd($subtotal, $lineTotal, 2);
        }

        $discountAmount = number_format((float) $discount, 2, '.', '');

        return [
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'grand_total' => bcsub($subtotal, $discountAmount, 2),
        ];
    }

    /**
     * @param  list<array{product_id: int, quantity: numeric-string|float|int, unit_price: numeric-string|float|int}>  $lines
     */
    private function syncLines(SalesInvoice $invoice, array $lines): void
    {
        $invoice->lines()->delete();

        foreach ($lines as $line) {
            $quantity = (string) $line['quantity'];
            $unitPrice = number_format((float) $line['unit_price'], 2, '.', '');

            $invoice->lines()->create([
                'product_id' => $line['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => bcmul($quantity, $unitPrice, 2),
            ]);
        }
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function distributorOptions(?int $includeDistributorId = null): array
    {
        return Distributor::query()
            ->where(function ($query) use ($includeDistributorId): void {
                $query->where('is_active', true);

                if ($includeDistributorId !== null) {
                    $query->orWhere('id', $includeDistributorId);
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
