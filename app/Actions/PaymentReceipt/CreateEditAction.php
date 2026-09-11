<?php

namespace App\Actions\PaymentReceipt;

use App\Enums\PaymentMethod;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Support\MoneyDisplay;
use App\Support\PaymentReceiptBalanceSnapshot;
use App\Support\QuantityDisplay;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function __construct(private PaymentReceiptBalanceSnapshot $snapshot) {}

    public function handle(Request $request, ?PaymentReceipt $paymentReceipt): Response
    {
        if ($paymentReceipt?->exists) {
            $paymentReceipt->load('distributor:id,name');

            $snapshot = $paymentReceipt->isPosted()
                ? $this->snapshot->forPosted($paymentReceipt)
                : $this->snapshot->preview($paymentReceipt->distributor_id, $paymentReceipt->amount);

            return Inertia::render('payment-receipts/create-edit', [
                'receipt' => [
                    'id' => $paymentReceipt->id,
                    'number' => $paymentReceipt->number,
                    'receipt_date' => $paymentReceipt->receipt_date?->toDateString(),
                    'distributor_id' => $paymentReceipt->distributor_id,
                    'payment_method' => $paymentReceipt->payment_method->value,
                    'amount' => QuantityDisplay::format($paymentReceipt->amount, 2),
                    'notes' => $paymentReceipt->notes,
                    'status' => $paymentReceipt->status->value,
                    'status_label' => $paymentReceipt->status->label(),
                    'is_posted' => $paymentReceipt->isPosted(),
                    'posted_at' => $paymentReceipt->posted_at?->toIso8601String(),
                    'balance_before' => MoneyDisplay::format($snapshot['before'], trim: true),
                    'balance_after' => MoneyDisplay::format($snapshot['after'], trim: true),
                ],
                'selected_distributor' => $paymentReceipt->distributor
                    ? [
                        'value' => $paymentReceipt->distributor->id,
                        'label' => $paymentReceipt->distributor->name,
                        'meta' => [
                            'balance' => $snapshot['before'],
                        ],
                    ]
                    : null,
                'payment_methods' => $this->paymentMethodOptions(),
                'can_edit' => $paymentReceipt->isDraft(),
                'can_post' => $paymentReceipt->isDraft() && bccomp((string) $paymentReceipt->amount, '0', 2) === 1,
                'can_cancel' => $paymentReceipt->isPosted(),
                'can_print' => $paymentReceipt->isPosted(),
            ]);
        }

        $selectedDistributor = $this->selectedDistributorFromRequest($request);

        return Inertia::render('payment-receipts/create-edit', [
            'receipt' => null,
            'selected_distributor' => $selectedDistributor,
            'payment_methods' => $this->paymentMethodOptions(),
            'can_edit' => true,
            'can_post' => false,
            'can_cancel' => false,
            'can_print' => false,
        ]);
    }

    /**
     * @return array{value: int, label: string, meta: array{balance: string}}|null
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
            'meta' => [
                'balance' => number_format((float) $distributor->balance(), 2, '.', ''),
            ],
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
