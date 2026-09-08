<?php

namespace App\Actions\Purchase;

use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(Purchase $purchase): RedirectResponse
    {
        $purchase->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المشترى بنجاح.']);

        return to_route('purchases.index');
    }
}
