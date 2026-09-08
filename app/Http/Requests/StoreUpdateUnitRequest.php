<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $unit = $this->route('unit');

        if ($unit?->exists) {
            return $this->user()?->can('update', $unit) ?? false;
        }

        return $this->user()?->can('create', Unit::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->symbol === '') {
            $this->merge(['symbol' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $unit = $this->route('unit');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Unit::class, 'name')->ignore($unit?->id),
            ],
            'symbol' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الوحدة',
            'symbol' => 'الرمز',
            'is_active' => 'الحالة',
        ];
    }
}
