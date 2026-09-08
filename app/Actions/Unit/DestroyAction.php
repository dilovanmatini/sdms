<?php

namespace App\Actions\Unit;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(Unit $unit): RedirectResponse
    {
        $unit->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف وحدة القياس بنجاح.']);

        return to_route('units.index');
    }
}
