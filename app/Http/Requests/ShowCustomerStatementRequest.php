<?php

namespace App\Http\Requests;

use App\Authorization\Ability;
use App\Models\Distributor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowCustomerStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Ability::ViewStatements->value) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'distributor_id' => ['required', 'integer', Rule::exists(Distributor::class, 'id')],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'distributor_id' => 'الموزع',
            'from_date' => 'من تاريخ',
            'to_date' => 'إلى تاريخ',
        ];
    }
}
