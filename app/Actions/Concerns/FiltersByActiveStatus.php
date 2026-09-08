<?php

namespace App\Actions\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait FiltersByActiveStatus
{
    protected function activeStatusFilter(Request $request): string
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'string', Rule::in(['0', '1'])],
        ]);

        return (string) ($validated['is_active'] ?? '');
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyActiveStatusFilter(Builder $query, string $isActive): void
    {
        if ($isActive === '') {
            return;
        }

        $query->where('is_active', $isActive === '1');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function activeStatusOptions(): array
    {
        return [
            ['value' => '', 'label' => 'الكل'],
            ['value' => '1', 'label' => 'نشط'],
            ['value' => '0', 'label' => 'غير نشط'],
        ];
    }
}
