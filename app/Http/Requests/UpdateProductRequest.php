<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->barcode === '') {
            $this->merge(['barcode' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique(Product::class, 'code')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique(Product::class, 'barcode')->ignore($product->id)],
            'name_ar' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'unit_id' => ['required', 'integer', Rule::exists(Unit::class, 'id')],
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
            'code' => 'رمز المنتج',
            'barcode' => 'الباركود',
            'name_ar' => 'الاسم العربي',
            'category_id' => 'الصنف',
            'unit_id' => 'وحدة القياس',
            'notes' => 'ملاحظات',
            'is_active' => 'الحالة',
        ];
    }
}
