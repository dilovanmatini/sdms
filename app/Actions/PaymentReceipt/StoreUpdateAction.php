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
                    'notes' => $data['notes'] ?? null,
                ]);

                $this->syncAllocations($paymentReceipt, $data['allocations']);
            });

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث سند القبض بنجاح.']);

            return to_route('payment-receipts.create-edit', $paymentReceipt);
        }

        $paymentReceipt = DB::transaction(function () use ($request): PaymentReceipt {
            $data = $request->validated();

            $paymentReceipt = PaymentReceipt::query()->create([
                'number' => $this->numbers->generate(DocumentType::Receipt),
                'receipt_date' => $data['receipt_date'],
                'distributor_id' => $data['distributor_id'],
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
                'status' => DocumentStatus::Draft,
            ]);

            $this->syncAllocations($paymentReceipt, $data['allocations']);

            return $paymentReceipt;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء سند القبض بنجاح.']);

        return to_route('payment-receipts.create-edit', $paymentReceipt);
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
}
