<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Models\SystemSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', SystemSetting::current()) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $nullableStrings = [
            'invoice_header',
            'invoice_footer',
            'receipt_header',
            'receipt_footer',
        ];

        $merged = [];

        foreach ($nullableStrings as $field) {
            if ($this->input($field) === '') {
                $merged[$field] = null;
            }
        }

        if ($this->has('remove_logo')) {
            $merged['remove_logo'] = $this->boolean('remove_logo');
        }

        if ($this->has('show_dashboard_numbers')) {
            $merged['show_dashboard_numbers'] = $this->boolean('show_dashboard_numbers');
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', Rule::enum(Currency::class)],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
            'invoice_header' => ['nullable', 'string', 'max:5000'],
            'invoice_footer' => ['nullable', 'string', 'max:5000'],
            'receipt_header' => ['nullable', 'string', 'max:5000'],
            'receipt_footer' => ['nullable', 'string', 'max:5000'],
            'show_dashboard_numbers' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'app_name' => 'اسم التطبيق',
            'currency' => 'العملة',
            'logo' => 'الشعار',
            'remove_logo' => 'إزالة الشعار',
            'invoice_header' => 'رأس الفاتورة',
            'invoice_footer' => 'تذييل الفاتورة',
            'receipt_header' => 'رأس السند',
            'receipt_footer' => 'تذييل السند',
            'show_dashboard_numbers' => 'إظهار أرقام الصفحة الرئيسية',
        ];
    }
}
