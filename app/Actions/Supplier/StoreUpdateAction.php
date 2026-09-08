<?php

namespace App\Actions\Supplier;

use App\Http\Requests\StoreUpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function handle(StoreUpdateSupplierRequest $request, ?Supplier $supplier): RedirectResponse
    {
        if ($supplier?->exists) {
            $supplier->update($request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث المورد بنجاح.']);

            return to_route('suppliers.create-edit', $supplier);
        }

        $supplier = Supplier::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء المورد بنجاح.']);

        return to_route('suppliers.create-edit', $supplier);
    }
}
