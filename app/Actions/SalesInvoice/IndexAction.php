<?php

namespace App\Actions\SalesInvoice;

use App\Actions\Concerns\FiltersDocumentIndex;
use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Enums\DocumentStatus;
use App\Models\Distributor;
use App\Models\SalesInvoice;
use App\Support\MoneyDisplay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use FiltersDocumentIndex;
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $filters = $this->documentIndexFilters($request, 'distributor_id', 'distributors');

        $invoices = SalesInvoice::query()
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
                $filters['from_date'] !== null,
                fn (Builder $query) => $query->whereDate('invoice_date', '>=', $filters['from_date']),
            )
            ->when(
                $filters['to_date'] !== null,
                fn (Builder $query) => $query->whereDate('invoice_date', '<=', $filters['to_date']),
            )
            ->latest('id')
            ->paginate($this->perPage($request, 'sales-invoices'))
            ->withQueryString()
            ->through(fn (SalesInvoice $invoice): array => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'distributor' => $invoice->distributor?->only(['id', 'name']),
                'subtotal' => MoneyDisplay::format($invoice->subtotal),
                'discount' => MoneyDisplay::format($invoice->discount),
                'grand_total' => MoneyDisplay::format($invoice->grand_total),
                'status' => $invoice->status->value,
                'status_label' => $invoice->status->label(),
                'is_posted' => $invoice->isPosted(),
                'can_edit' => $invoice->isDraft(),
                'can_delete' => $invoice->isDraft(),
                'can_cancel' => $invoice->isPosted(),
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

        return Inertia::render('sales-invoices/index', [
            'invoices' => $invoices,
            'selected_distributor' => $selectedDistributor,
            'filters' => [
                'search' => $filters['search'],
                'status' => $filters['status'],
                'distributor_id' => $filters['party_id'],
                'from_date' => $filters['from_date'],
                'to_date' => $filters['to_date'],
            ],
            'status_options' => $this->documentStatusOptions(),
        ]);
    }
}
