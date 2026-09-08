<?php

namespace App\Http\Requests;

use App\Models\Distributor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateDistributorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $distributor = $this->route('distributor');

        if ($distributor?->exists) {
            return $this->user()?->can('update', $distributor) ?? false;
        }

        return $this->user()?->can('create', Distributor::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->credit_limit === '') {
            $this->merge(['credit_limit' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الموزع',
            'contact_person' => 'جهة الاتصال',
            'phone' => 'الهاتف',
            'address' => 'العنوان',
            'credit_limit' => 'حد الائتمان',
            'notes' => 'ملاحظات',
            'is_active' => 'الحالة',
        ];
    }
}
