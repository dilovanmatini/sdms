<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Purchase::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->notes === '') {
            $this->merge(['notes' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'purchase_date' => ['required', 'date'],
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists(Supplier::class, 'id')->where('is_active', true),
            ],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(Product::class, 'id')->where('is_active', true),
            ],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'purchase_date' => 'تاريخ الشراء',
            'supplier_id' => 'المورد',
            'notes' => 'ملاحظات',
            'lines' => 'البنود',
            'lines.*.product_id' => 'المنتج',
            'lines.*.quantity' => 'الكمية',
        ];
    }
}
