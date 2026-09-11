<?php

namespace App\Actions\PaymentReceipt;

use App\Actions\Concerns\FiltersDocumentIndex;
use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Enums\DocumentStatus;
use App\Enums\PaymentMethod;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Support\MoneyDisplay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use FiltersDocumentIndex;
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $filters = $this->documentIndexFilters($request, 'distributor_id', 'distributors');
        $paymentMethod = $this->paymentMethodFilter($request);

        $receipts = PaymentReceipt::query()
            ->with(['distributor:id,name'])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('distributor', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                $filters['status'] !== '',
                fn (Builder $query) => $query->where('status', $filters['status']),
                fn (Builder $query) => $query->where('status', '!=', DocumentStatus::Cancelled),
            )
            ->when(
                $filters['party_id'] !== null,
                fn (Builder $query) => $query->where('distributor_id', $filters['party_id']),
            )
            ->when(
                $paymentMethod !== '',
                fn (Builder $query) => $query->where('payment_method', $paymentMethod),
            )
            ->when(
                $filters['from_date'] !== null,
                fn (Builder $query) => $query->whereDate('receipt_date', '>=', $filters['from_date']),
            )
            ->when(
                $filters['to_date'] !== null,
                fn (Builder $query) => $query->whereDate('receipt_date', '<=', $filters['to_date']),
            )
            ->latest('id')
            ->paginate($this->perPage($request, 'payment-receipts'))
            ->withQueryString()
            ->through(fn (PaymentReceipt $receipt): array => [
                'id' => $receipt->id,
                'number' => $receipt->number,
                'receipt_date' => $receipt->receipt_date?->toDateString(),
                'distributor' => $receipt->distributor?->only(['id', 'name']),
                'payment_method' => $receipt->payment_method->value,
                'payment_method_label' => $receipt->payment_method->label(),
                'total_amount' => MoneyDisplay::format($receipt->amount ?? 0),
                'status' => $receipt->status->value,
                'status_label' => $receipt->status->label(),
                'is_posted' => $receipt->isPosted(),
                'can_edit' => $receipt->isDraft(),
                'can_delete' => $receipt->isDraft(),
                'can_cancel' => $receipt->isPosted(),
            ]);

        $selectedDistributor = null;

        if ($filters['party_id'] !== null) {
            $distributor = Distributor::query()->find($filters['party_id']);

            if ($distributor !== null) {
                $selectedDistributor = [
                    'value' => $distributor->id,
                    'label' => $distributor->name,
                ];
            }
        }

        return Inertia::render('payment-receipts/index', [
            'receipts' => $receipts,
            'selected_distributor' => $selectedDistributor,
            'filters' => [
                'search' => $filters['search'],
                'status' => $filters['status'],
                'distributor_id' => $filters['party_id'],
                'payment_method' => $paymentMethod,
                'from_date' => $filters['from_date'],
                'to_date' => $filters['to_date'],
            ],
            'status_options' => $this->documentStatusOptions(),
            'payment_method_options' => $this->paymentMethodOptions(),
        ]);
    }

    private function paymentMethodFilter(Request $request): string
    {
        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', Rule::enum(PaymentMethod::class)],
        ], [], [
            'payment_method' => 'طريقة الدفع',
        ]);

        return (string) ($validated['payment_method'] ?? '');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function paymentMethodOptions(): array
    {
        return [
            ['value' => '', 'label' => 'كل طرق الدفع'],
            ...array_map(
                fn (PaymentMethod $method): array => [
                    'value' => $method->value,
                    'label' => $method->label(),
                ],
                PaymentMethod::cases(),
            ),
        ];
    }
}
