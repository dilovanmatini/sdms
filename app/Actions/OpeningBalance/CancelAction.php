<?php

namespace App\Actions\OpeningBalance;

use App\Http\Requests\CancelOpeningBalanceRequest;
use App\Models\OpeningBalance;
use App\Services\OpeningBalanceCanceller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class CancelAction
{
    public function __construct(private OpeningBalanceCanceller $canceller) {}

    public function handle(CancelOpeningBalanceRequest $request, OpeningBalance $openingBalance): RedirectResponse
    {
        try {
            $this->canceller->cancel($openingBalance);
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إلغاء المبلغ غير المسدد بنجاح.']);

        return to_route('opening-balances.index');
    }
}
