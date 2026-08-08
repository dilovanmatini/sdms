<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDistributorRequest;
use App\Http\Requests\UpdateDistributorRequest;
use App\Models\Distributor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DistributorController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Distributor::class);

        $search = $request->string('search')->trim()->toString();

        $distributors = Distributor::query()
            ->withCount(['salesInvoices', 'paymentReceipts'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Distributor $distributor): array => [
                'id' => $distributor->id,
                'name' => $distributor->name,
                'contact_person' => $distributor->contact_person,
                'phone' => $distributor->phone,
                'credit_limit' => $distributor->credit_limit,
                'is_active' => $distributor->is_active,
                'can_delete' => $distributor->sales_invoices_count === 0
                    && $distributor->payment_receipts_count === 0,
            ]);

        return Inertia::render('distributors/index', [
            'distributors' => $distributors,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Distributor::class);

        return Inertia::render('distributors/create');
    }

    public function store(StoreDistributorRequest $request): RedirectResponse
    {
        Distributor::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء الموزع بنجاح.']);

        return to_route('distributors.index');
    }

    public function edit(Distributor $distributor): Response
    {
        $this->authorize('update', $distributor);

        return Inertia::render('distributors/edit', [
            'distributor' => [
                'id' => $distributor->id,
                'name' => $distributor->name,
                'contact_person' => $distributor->contact_person,
                'phone' => $distributor->phone,
                'address' => $distributor->address,
                'credit_limit' => $distributor->credit_limit,
                'notes' => $distributor->notes,
                'is_active' => $distributor->is_active,
            ],
        ]);
    }

    public function update(UpdateDistributorRequest $request, Distributor $distributor): RedirectResponse
    {
        $distributor->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث الموزع بنجاح.']);

        return to_route('distributors.index');
    }

    public function destroy(Distributor $distributor): RedirectResponse
    {
        $this->authorize('delete', $distributor);

        $distributor->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف الموزع بنجاح.']);

        return to_route('distributors.index');
    }
}
