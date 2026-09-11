<?php

namespace App\Actions\OpeningBalance;

use App\Models\Distributor;
use App\Models\OpeningBalance;
use App\Support\QuantityDisplay;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateEditAction
{
    public function handle(Request $request, ?OpeningBalance $openingBalance): Response
    {
        if ($openingBalance?->exists) {
            $openingBalance->load('distributor:id,name');

            return Inertia::render('opening-balances/create-edit', [
                'opening_balance' => [
                    'id' => $openingBalance->id,
                    'number' => $openingBalance->number,
                    'entry_date' => $openingBalance->entry_date?->toDateString(),
                    'distributor_id' => $openingBalance->distributor_id,
                    'amount' => QuantityDisplay::format($openingBalance->amount, 2),
                    'notes' => $openingBalance->notes,
                    'status' => $openingBalance->status->value,
                    'status_label' => $openingBalance->status->label(),
                    'is_posted' => $openingBalance->isPosted(),
                    'posted_at' => $openingBalance->posted_at?->toIso8601String(),
                ],
                'selected_distributor' => $openingBalance->distributor
                    ? [
                        'value' => $openingBalance->distributor->id,
                        'label' => $openingBalance->distributor->name,
                    ]
                    : null,
                'can_edit' => $openingBalance->isDraft(),
                'can_post' => $openingBalance->isDraft() && bccomp((string) $openingBalance->amount, '0', 2) === 1,
                'can_cancel' => $openingBalance->isPosted(),
            ]);
        }

        return Inertia::render('opening-balances/create-edit', [
            'opening_balance' => null,
            'selected_distributor' => $this->selectedDistributorFromRequest($request),
            'can_edit' => true,
            'can_post' => false,
            'can_cancel' => false,
        ]);
    }

    /**
     * @return array{value: int, label: string}|null
     */
    private function selectedDistributorFromRequest(Request $request): ?array
    {
        if (! $request->filled('distributor_id')) {
            return null;
        }

        $distributor = Distributor::query()
            ->whereKey($request->integer('distributor_id'))
            ->first(['id', 'name']);

        if ($distributor === null) {
            return null;
        }

        return [
            'value' => $distributor->id,
            'label' => $distributor->name,
        ];
    }
}
