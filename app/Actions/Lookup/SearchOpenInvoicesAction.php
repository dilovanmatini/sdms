<?php

namespace App\Actions\Lookup;

use App\Actions\Lookup\Concerns\ResolvesLookupSearch;
use App\Enums\DocumentStatus;
use App\Models\SalesInvoice;
use App\Support\MoneyDisplay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SearchOpenInvoicesAction
{
    use ResolvesLookupSearch;

    public function handle(Request $request): JsonResponse
    {
        if (! $request->filled('distributor_id')) {
            throw ValidationException::withMessages([
                'distributor_id' => 'يجب اختيار الموزع أولاً.',
            ]);
        }

        $distributorId = $request->integer('distributor_id');
        $search = $this->searchTerm($request);
        $limit = $this->resultLimit($request);
        $includeIds = $this->includeIds($request);

        $invoices = SalesInvoice::query()
            ->where('distributor_id', $distributorId)
            ->where('status', DocumentStatus::Posted)
            ->withSum([
                'allocations as posted_allocated_amount' => function ($query): void {
                    $query->whereHas('paymentReceipt', function ($query): void {
                        $query->where('status', DocumentStatus::Posted);
                    });
                },
            ], 'amount')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('number', 'like', "%{$search}%");
            })
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->limit(max($limit * 3, 50))
            ->get()
            ->map(function (SalesInvoice $invoice) use ($includeIds): ?array {
                $allocated = number_format((float) ($invoice->posted_allocated_amount ?? 0), 2, '.', '');
                $remaining = bcsub((string) $invoice->grand_total, $allocated, 2);

                if (bccomp($remaining, '0', 2) !== 1 && ! in_array($invoice->id, $includeIds, true)) {
                    return null;
                }

                return [
                    'value' => $invoice->id,
                    'label' => "{$invoice->number} — متبقي ".MoneyDisplay::withSymbol($remaining),
                    'meta' => [
                        'number' => $invoice->number,
                        'invoice_date' => $invoice->invoice_date?->toDateString(),
                        'distributor_id' => $invoice->distributor_id,
                        'grand_total' => (string) $invoice->grand_total,
                        'remaining' => $remaining,
                    ],
                ];
            })
            ->filter()
            ->take($limit)
            ->values()
            ->all();

        return response()->json([
            'data' => $invoices,
        ]);
    }
}
