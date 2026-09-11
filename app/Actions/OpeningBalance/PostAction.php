<?php

namespace App\Actions\OpeningBalance;

use App\Http\Requests\PostOpeningBalanceRequest;
use App\Models\OpeningBalance;
use App\Services\OpeningBalancePoster;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class PostAction
{
    public function __construct(private OpeningBalancePoster $poster) {}

    public function handle(PostOpeningBalanceRequest $request, OpeningBalance $openingBalance): RedirectResponse
    {
        try {
            $this->poster->post($openingBalance, $request->user());
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم التأكيد النهائي للمبلغ غير المسدد بنجاح.']);

        return to_route('opening-balances.index');
    }
}
