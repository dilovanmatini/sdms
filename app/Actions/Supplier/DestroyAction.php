<?php

namespace App\Actions\Supplier;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المورد بنجاح.']);

        return to_route('suppliers.index');
    }
}
