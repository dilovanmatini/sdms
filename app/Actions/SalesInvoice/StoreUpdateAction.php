<?php

namespace App\Actions\SalesInvoice;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Requests\StoreUpdateSalesInvoiceRequest;
use App\Models\SalesInvoice;
use App\Services\DocumentNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function __construct(private DocumentNumberGenerator $numbers) {}

    public function handle(StoreUpdateSalesInvoiceRequest $request, ?SalesInvoice $salesInvoice): RedirectResponse
    {
        if ($salesInvoice?->exists) {
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

            return to_route('sales-invoices.create-edit', $salesInvoice);
        }

        $salesInvoice = DB::transaction(function () use ($request): SalesInvoice {
            $data = $request->validated();
            $totals = $this->calculateTotals($data['lines'], $data['discount']);

            $salesInvoice = SalesInvoice::query()->create([
                'number' => $this->numbers->generate(DocumentType::Invoice),
                'invoice_date' => $data['invoice_date'],
                'distributor_id' => $data['distributor_id'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'grand_total' => $totals['grand_total'],
                'status' => DocumentStatus::Draft,
            ]);

            $this->syncLines($salesInvoice, $data['lines']);

            return $salesInvoice;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء فاتورة المبيعات بنجاح.']);

        return to_route('sales-invoices.create-edit', $salesInvoice);
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
}
