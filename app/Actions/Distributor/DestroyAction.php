<?php

namespace App\Actions\Distributor;

use App\Models\Distributor;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(Distributor $distributor): RedirectResponse
    {
        $distributor->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف الموزع بنجاح.']);

        return to_route('distributors.index');
    }
}
