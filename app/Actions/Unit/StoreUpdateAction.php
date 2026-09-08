<?php

namespace App\Actions\Unit;

use App\Http\Requests\StoreUpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function handle(StoreUpdateUnitRequest $request, ?Unit $unit): RedirectResponse
    {
        if ($unit?->exists) {
            $unit->update($request->validated());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث وحدة القياس بنجاح.']);

            return to_route('units.create-edit', $unit);
        }

        $unit = Unit::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء وحدة القياس بنجاح.']);

        return to_route('units.create-edit', $unit);
    }
}
