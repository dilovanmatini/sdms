<?php

namespace App\Actions\Distributor;

use App\Http\Requests\StoreUpdateDistributorRequest;
use App\Models\Distributor;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function handle(StoreUpdateDistributorRequest $request, ?Distributor $distributor): RedirectResponse
    {
        if ($distributor?->exists) {
            $distributor->update($request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث الموزع بنجاح.']);

            return to_route('distributors.create-edit', $distributor);
        }

        $distributor = Distributor::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء الموزع بنجاح.']);

        return to_route('distributors.create-edit', $distributor);
    }
}
