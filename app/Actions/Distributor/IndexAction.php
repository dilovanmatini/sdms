<?php

namespace App\Actions\Distributor;

use App\Actions\Concerns\FiltersByActiveStatus;
use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use FiltersByActiveStatus;
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $isActive = $this->activeStatusFilter($request);

        $distributors = Distributor::query()
            ->withCount(['salesInvoices', 'paymentReceipts', 'openingBalances'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->tap(fn ($query) => $this->applyActiveStatusFilter($query, $isActive))
            ->latest('id')
            ->paginate($this->perPage($request, 'distributors'))
            ->withQueryString()
            ->through(fn (Distributor $distributor): array => [
                'id' => $distributor->id,
                'name' => $distributor->name,
                'contact_person' => $distributor->contact_person,
                'phone' => $distributor->phone,
                'credit_limit' => $distributor->credit_limit,
                'is_active' => $distributor->is_active,
                'can_delete' => $distributor->sales_invoices_count === 0
                    && $distributor->payment_receipts_count === 0
                    && $distributor->opening_balances_count === 0,
            ]);

        return Inertia::render('distributors/index', [
            'distributors' => $distributors,
            'filters' => [
                'search' => $search,
                'is_active' => $isActive,
            ],
            'active_status_options' => $this->activeStatusOptions(),
        ]);
    }
}
