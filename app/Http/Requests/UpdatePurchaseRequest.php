<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Purchase|null $purchase */
        $purchase = $this->route('purchase');

        return $purchase !== null
            && ($this->user()?->can('update', $purchase) ?? false);
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
        /** @var Purchase $purchase */
        $purchase = $this->route('purchase');
        $existingProductIds = $purchase->lines()->pluck('product_id')->all();

        return [
            'purchase_date' => ['required', 'date'],
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists(Supplier::class, 'id')->where(function ($query) use ($purchase): void {
                    $query->where('is_active', true)
                        ->orWhere('id', $purchase->supplier_id);
                }),
            ],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(Product::class, 'id')->where(function ($query) use ($existingProductIds): void {
                    $query->where('is_active', true);

                    if ($existingProductIds !== []) {
                        $query->orWhereIn('id', $existingProductIds);
                    }
                }),
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
