<?php

namespace App\Actions\PaymentReceipt;

use App\Enums\PaymentMethod;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptAllocation;
use App\Support\QuantityDisplay;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function handle(Request $request, ?PaymentReceipt $paymentReceipt): Response
    {
        if ($paymentReceipt?->exists) {
            $paymentReceipt->load(['allocations.salesInvoice:id,number,grand_total', 'distributor:id,name']);

            return Inertia::render('payment-receipts/create-edit', [
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
                        'amount' => QuantityDisplay::format($allocation->amount, 2),
                        'invoice' => $allocation->salesInvoice?->only(['id', 'number', 'grand_total']),
                    ])->values()->all(),
                ],
                'selected_distributor' => $paymentReceipt->distributor
                    ? [
                        'value' => $paymentReceipt->distributor->id,
                        'label' => $paymentReceipt->distributor->name,
                    ]
                    : null,
                'selected_invoices' => $paymentReceipt->allocations
                    ->map(function (PaymentReceiptAllocation $allocation): ?array {
                        if ($allocation->salesInvoice === null) {
                            return null;
                        }

                        return [
                            'value' => $allocation->salesInvoice->id,
                            'label' => $allocation->salesInvoice->number,
                            'meta' => [
                                'number' => $allocation->salesInvoice->number,
                                'grand_total' => QuantityDisplay::format($allocation->salesInvoice->grand_total, 2),
                                'remaining' => QuantityDisplay::format($allocation->amount, 2),
                            ],
                        ];
                    })
                    ->filter()
                    ->unique('value')
                    ->values()
                    ->all(),
                'payment_methods' => $this->paymentMethodOptions(),
                'can_edit' => $paymentReceipt->isDraft(),
                'can_post' => $paymentReceipt->isDraft() && $paymentReceipt->allocations->isNotEmpty(),
                'can_cancel' => $paymentReceipt->isPosted(),
                'can_print' => $paymentReceipt->isPosted(),
            ]);
        }

        return Inertia::render('payment-receipts/create-edit', [
            'receipt' => null,
            'selected_distributor' => $this->selectedDistributorFromRequest($request),
            'selected_invoices' => [],
            'payment_methods' => $this->paymentMethodOptions(),
            'can_edit' => true,
            'can_post' => false,
            'can_cancel' => false,
            'can_print' => false,
        ]);
    }

    /**
     * @return array{value: int, label: string}|null
     */
    private function selectedDistributorFromRequest(Request $request): ?array
    {
        if (! $request->filled('distributor_id')) {
            return null;
        }

        $distributor = Distributor::query()
            ->whereKey($request->integer('distributor_id'))
            ->first(['id', 'name']);

        if ($distributor === null) {
            return null;
        }

        return [
            'value' => $distributor->id,
            'label' => $distributor->name,
        ];
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
}
