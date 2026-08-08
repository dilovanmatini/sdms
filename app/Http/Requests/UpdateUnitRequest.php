<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Unit $unit */
        $unit = $this->route('unit');

        return $this->user()?->can('update', $unit) ?? false;
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
        /** @var Unit $unit */
        $unit = $this->route('unit');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Unit::class, 'name')->ignore($unit->id)],
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
