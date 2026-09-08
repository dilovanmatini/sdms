<?php

namespace App\Actions\Concerns;

use App\Enums\DocumentStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait FiltersDocumentIndex
{
    /**
     * @return array{
     *     search: string,
     *     status: string,
     *     party_id: int|null,
     *     from_date: string|null,
     *     to_date: string|null
     * }
     */
    protected function documentIndexFilters(Request $request, string $partyKey, string $partyTable): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in([
                DocumentStatus::Draft->value,
                DocumentStatus::Posted->value,
                DocumentStatus::Cancelled->value,
            ])],
            $partyKey => ['nullable', 'integer', "exists:{$partyTable},id"],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ], [], [
            'search' => 'البحث',
            'status' => 'الحالة',
            $partyKey => $partyKey === 'supplier_id' ? 'المورد' : 'الموزع',
            'from_date' => 'من تاريخ',
            'to_date' => 'إلى تاريخ',
        ]);

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'status' => (string) ($validated['status'] ?? ''),
            'party_id' => isset($validated[$partyKey]) ? (int) $validated[$partyKey] : null,
            'from_date' => isset($validated['from_date']) && $validated['from_date'] !== ''
                ? (string) $validated['from_date']
                : null,
            'to_date' => isset($validated['to_date']) && $validated['to_date'] !== ''
                ? (string) $validated['to_date']
                : null,
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function documentStatusOptions(): array
    {
        return [
            ['value' => '', 'label' => 'غير الملغاة'],
            ['value' => DocumentStatus::Draft->value, 'label' => DocumentStatus::Draft->label()],
            ['value' => DocumentStatus::Posted->value, 'label' => DocumentStatus::Posted->label()],
            ['value' => DocumentStatus::Cancelled->value, 'label' => DocumentStatus::Cancelled->label()],
        ];
    }
}
