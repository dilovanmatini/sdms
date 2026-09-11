<?php

namespace App\Actions\OpeningBalance;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Requests\StoreUpdateOpeningBalanceRequest;
use App\Models\OpeningBalance;
use App\Services\DocumentNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StoreUpdateAction
{
    public function __construct(private DocumentNumberGenerator $numbers) {}

    public function handle(StoreUpdateOpeningBalanceRequest $request, ?OpeningBalance $openingBalance): RedirectResponse
    {
        if ($openingBalance?->exists) {
            $openingBalance->update([
                'entry_date' => $request->validated('entry_date'),
                'distributor_id' => $request->validated('distributor_id'),
                'amount' => $this->normalizedAmount($request->validated('amount')),
                'notes' => $request->validated('notes'),
            ]);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث المبلغ غير المسدد بنجاح.']);

            return to_route('opening-balances.create-edit', $openingBalance);
        }

        $openingBalance = DB::transaction(function () use ($request): OpeningBalance {
            $data = $request->validated();

            return OpeningBalance::query()->create([
                'number' => $this->numbers->generate(DocumentType::OpeningBalance),
                'entry_date' => $data['entry_date'],
                'distributor_id' => $data['distributor_id'],
                'amount' => $this->normalizedAmount($data['amount']),
                'notes' => $data['notes'] ?? null,
                'status' => DocumentStatus::Draft,
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إنشاء المبلغ غير المسدد بنجاح.']);

        return to_route('opening-balances.create-edit', $openingBalance);
    }

    private function normalizedAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
