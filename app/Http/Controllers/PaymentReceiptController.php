<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Http\Requests\PostPaymentReceiptRequest;
use App\Http\Requests\StorePaymentReceiptRequest;
use App\Http\Requests\UpdatePaymentReceiptRequest;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptAllocation;
use App\Models\SalesInvoice;
use App\Services\DocumentNumberGenerator;
use App\Services\PaymentReceiptPoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class PaymentReceiptController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PaymentReceipt::class);

        $search = $request->string('search')->trim()->toString();

        $receipts = PaymentReceipt::query()
            ->with(['distributor:id,name'])
            ->withSum('allocations as total_amount', 'amount')
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
            ->through(fn (PaymentReceipt $receipt): array => [
                'id' => $receipt->id,
                'number' => $receipt->number,
                'receipt_date' => $receipt->receipt_date?->toDateString(),
                'distributor' => $receipt->distributor?->only(['id', 'name']),
                'payment_method' => $receipt->payment_method->value,
                'payment_method_label' => $receipt->payment_method->label(),
                'total_amount' => number_format((float) ($receipt->total_amount ?? 0), 2, '.', ''),
                'status' => $receipt->status->value,
                'status_label' => $receipt->status->label(),
                'is_posted' => $receipt->isPosted(),
                'can_edit' => ! $receipt->isPosted(),
                'can_delete' => ! $receipt->isPosted(),
            ]);

        return Inertia::render('payment-receipts/index', [
            'receipts' => $receipts,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PaymentReceipt::class);

        return Inertia::render('payment-receipts/create', [
            'distributors' => $this->distributorOptions(),
            'payment_methods' => $this->paymentMethodOptions(),
            'open_invoices' => $this->openInvoiceOptions(),
        ]);
    }

    public function store(
        StorePaymentReceiptRequest $request,
        DocumentNumberGenerator $numbers,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $numbers): void {
            $data = $request->validated();

            $receipt = PaymentReceipt::query()->create([
                'number' => $numbers->generate(DocumentType::Receipt),
                'receipt_date' => $data['receipt_date'],
                'distributor_id' => $data['distributor_id'],
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
                'status' => DocumentStatus::Draft,
            ]);

            $this->syncAllocations($receipt, $data['allocations']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء سند القبض بنجاح.']);

        return to_route('payment-receipts.index');
    }

    public function edit(PaymentReceipt $paymentReceipt): Response
    {
        $this->authorize('view', $paymentReceipt);

        $paymentReceipt->load(['allocations.salesInvoice:id,number,grand_total', 'distributor:id,name']);

        return Inertia::render('payment-receipts/edit', [
            'receipt' => [
                'id' => $paymentReceipt->id,
                'number' => $paymentReceipt->number,
                'receipt_date' => $paymentReceipt->receipt_date?->toDateString(),
                'distributor_id' => $paymentReceipt->distributor_id,
                'payment_method' => $paymentReceipt->payment_method->value,
                'notes' => $paymentReceipt->notes,
                'status' => $paymentReceipt->status->value,
                'status_label' => $paymentReceipt->status->label(),
                'is_posted' => $paymentReceipt->isPosted(),
                'posted_at' => $paymentReceipt->posted_at?->toIso8601String(),
                'allocations' => $paymentReceipt->allocations->map(fn (PaymentReceiptAllocation $allocation): array => [
                    'sales_invoice_id' => $allocation->sales_invoice_id,
                    'amount' => (string) $allocation->amount,
                    'invoice' => $allocation->salesInvoice?->only(['id', 'number', 'grand_total']),
                ])->values()->all(),
            ],
            'distributors' => $this->distributorOptions($paymentReceipt->distributor_id),
            'payment_methods' => $this->paymentMethodOptions(),
            'open_invoices' => $this->openInvoiceOptions(
                includeInvoiceIds: $paymentReceipt->allocations->pluck('sales_invoice_id')->all(),
            ),
            'can_edit' => ! $paymentReceipt->isPosted(),
            'can_post' => ! $paymentReceipt->isPosted() && $paymentReceipt->allocations->isNotEmpty(),
        ]);
    }

    public function update(
        UpdatePaymentReceiptRequest $request,
        PaymentReceipt $paymentReceipt,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $paymentReceipt): void {
            $data = $request->validated();

            $paymentReceipt->update([
                'receipt_date' => $data['receipt_date'],
                'distributor_id' => $data['distributor_id'],
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncAllocations($paymentReceipt, $data['allocations']);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث سند القبض بنجاح.']);

        return to_route('payment-receipts.index');
    }

    public function destroy(PaymentReceipt $paymentReceipt): RedirectResponse
    {
        $this->authorize('delete', $paymentReceipt);

        $paymentReceipt->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف سند القبض بنجاح.']);

        return to_route('payment-receipts.index');
    }

    public function post(
        PostPaymentReceiptRequest $request,
        PaymentReceipt $paymentReceipt,
        PaymentReceiptPoster $poster,
    ): RedirectResponse {
        try {
            $poster->post($paymentReceipt, $request->user());
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم ترحيل سند القبض بنجاح.']);

        return to_route('payment-receipts.index');
    }

    /**
     * @param  list<array{sales_invoice_id: int, amount: numeric-string|float|int}>  $allocations
     */
    private function syncAllocations(PaymentReceipt $receipt, array $allocations): void
    {
        $receipt->allocations()->delete();

        foreach ($allocations as $allocation) {
            $receipt->allocations()->create([
                'sales_invoice_id' => $allocation['sales_invoice_id'],
                'amount' => number_format((float) $allocation['amount'], 2, '.', ''),
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
     * @return list<array{value: string, label: string}>
     */
    private function paymentMethodOptions(): array
    {
        return array_map(
            fn (PaymentMethod $method): array => [
                'value' => $method->value,
                'label' => $method->label(),
            ],
            PaymentMethod::cases(),
        );
    }

    /**
     * @param  list<int>  $includeInvoiceIds
     * @return list<array{id: int, number: string, invoice_date: string|null, distributor_id: int, grand_total: string, remaining: string}>
     */
    private function openInvoiceOptions(array $includeInvoiceIds = []): array
    {
        return SalesInvoice::query()
            ->where('status', DocumentStatus::Posted)
            ->withSum([
                'allocations as posted_allocated_amount' => function ($query): void {
                    $query->whereHas('paymentReceipt', function ($query): void {
                        $query->where('status', DocumentStatus::Posted);
                    });
                },
            ], 'amount')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (SalesInvoice $invoice) use ($includeInvoiceIds): ?array {
                $allocated = number_format((float) ($invoice->posted_allocated_amount ?? 0), 2, '.', '');
                $remaining = bcsub((string) $invoice->grand_total, $allocated, 2);

                if (bccomp($remaining, '0', 2) !== 1 && ! in_array($invoice->id, $includeInvoiceIds, true)) {
                    return null;
                }

                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'invoice_date' => $invoice->invoice_date?->toDateString(),
                    'distributor_id' => $invoice->distributor_id,
                    'grand_total' => (string) $invoice->grand_total,
                    'remaining' => $remaining,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
