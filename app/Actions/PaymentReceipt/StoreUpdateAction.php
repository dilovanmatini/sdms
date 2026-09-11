<?php

namespace App\Actions\PaymentReceipt;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Requests\StoreUpdatePaymentReceiptRequest;
use App\Models\PaymentReceipt;
use App\Services\DocumentNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function __construct(private DocumentNumberGenerator $numbers) {}

    public function handle(StoreUpdatePaymentReceiptRequest $request, ?PaymentReceipt $paymentReceipt): RedirectResponse
    {
        if ($paymentReceipt?->exists) {
            DB::transaction(function () use ($request, $paymentReceipt): void {
                $data = $request->validated();

                $paymentReceipt->update([
                    'receipt_date' => $data['receipt_date'],
                    'distributor_id' => $data['distributor_id'],
                    'payment_method' => $data['payment_method'],
                    'amount' => $this->normalizedAmount($data['amount']),
                    'notes' => $data['notes'] ?? null,
                ]);

                $paymentReceipt->allocations()->delete();
            });

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث سند القبض بنجاح.']);

            return to_route('payment-receipts.create-edit', $paymentReceipt);
        }

        $paymentReceipt = DB::transaction(function () use ($request): PaymentReceipt {
            $data = $request->validated();

            return PaymentReceipt::query()->create([
                'number' => $this->numbers->generate(DocumentType::Receipt),
                'receipt_date' => $data['receipt_date'],
                'distributor_id' => $data['distributor_id'],
                'payment_method' => $data['payment_method'],
                'amount' => $this->normalizedAmount($data['amount']),
                'notes' => $data['notes'] ?? null,
                'status' => DocumentStatus::Draft,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء سند القبض بنجاح.']);

        return to_route('payment-receipts.create-edit', $paymentReceipt);
    }

    private function normalizedAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
