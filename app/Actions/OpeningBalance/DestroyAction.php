<?php

namespace App\Actions\OpeningBalance;

use App\Models\OpeningBalance;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(OpeningBalance $openingBalance): RedirectResponse
    {
        $openingBalance->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المبلغ غير المسدد بنجاح.']);

        return to_route('opening-balances.index');
    }
}
