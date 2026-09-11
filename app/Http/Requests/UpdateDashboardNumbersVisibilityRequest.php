<?php

namespace App\Http\Requests;

use App\Authorization\Ability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDashboardNumbersVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Ability::ViewDashboard->value) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'show_dashboard_numbers' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'show_dashboard_numbers' => 'إظهار أرقام الصفحة الرئيسية',
        ];
    }
}
