<?php

namespace App\Actions\OpeningBalance;

use App\Actions\Concerns\FiltersDocumentIndex;
use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Enums\DocumentStatus;
use App\Models\Distributor;
use App\Models\OpeningBalance;
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

        $openingBalances = OpeningBalance::query()
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
                fn (Builder $query) => $query->whereDate('entry_date', '>=', $filters['from_date']),
            )
            ->when(
                $filters['to_date'] !== null,
                fn (Builder $query) => $query->whereDate('entry_date', '<=', $filters['to_date']),
            )
            ->latest('id')
            ->paginate($this->perPage($request, 'opening-balances'))
            ->withQueryString()
            ->through(fn (OpeningBalance $openingBalance): array => [
                'id' => $openingBalance->id,
                'number' => $openingBalance->number,
                'entry_date' => $openingBalance->entry_date?->toDateString(),
                'distributor' => $openingBalance->distributor?->only(['id', 'name']),
                'amount' => MoneyDisplay::format($openingBalance->amount),
                'status' => $openingBalance->status->value,
                'status_label' => $openingBalance->status->label(),
                'is_posted' => $openingBalance->isPosted(),
                'can_edit' => $openingBalance->isDraft(),
                'can_delete' => $openingBalance->isDraft(),
                'can_cancel' => $openingBalance->isPosted(),
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

        return Inertia::render('opening-balances/index', [
            'opening_balances' => $openingBalances,
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
