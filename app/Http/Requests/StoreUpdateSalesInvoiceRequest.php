<?php

namespace App\Http\Requests;

use App\Models\Distributor;
use App\Models\Product;
use App\Models\SalesInvoice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUpdateSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('sales_invoice');

        if ($invoice?->exists) {
            return $this->user()?->can('update', $invoice) ?? false;
        }

        return $this->user()?->can('create', SalesInvoice::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->notes === '') {
            $this->merge(['notes' => null]);
        }

        if ($this->discount === '' || $this->discount === null) {
            $this->merge(['discount' => 0]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $invoice = $this->route('sales_invoice');

        if ($invoice?->exists) {
            $existingProductIds = $invoice->lines()->pluck('product_id')->all();

            return [
                'invoice_date' => ['required', 'date'],
                'distributor_id' => [
                    'required',
                    'integer',
                    Rule::exists(Distributor::class, 'id')->where(function ($query) use ($invoice): void {
                        $query->where('is_active', true)
                            ->orWhere('id', $invoice->distributor_id);
                    }),
                ],
                'notes' => ['nullable', 'string'],
                'discount' => ['required', 'numeric', 'min:0'],
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
                'lines.*.unit_price' => ['required', 'numeric', 'gte:0'],
            ];
        }

        return [
            'invoice_date' => ['required', 'date'],
            'distributor_id' => [
                'required',
                'integer',
                Rule::exists(Distributor::class, 'id')->where('is_active', true),
            ],
            'notes' => ['nullable', 'string'],
            'discount' => ['required', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(Product::class, 'id')->where('is_active', true),
            ],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'gte:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $subtotal = '0';

            foreach ($this->input('lines', []) as $line) {
                $lineTotal = bcmul((string) $line['quantity'], (string) $line['unit_price'], 2);
                $subtotal = bcadd($subtotal, $lineTotal, 2);
            }

            if (bccomp((string) $this->input('discount'), $subtotal, 2) === 1) {
                $validator->errors()->add('discount', 'الخصم لا يمكن أن يتجاوز مجموع العناصر.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'invoice_date' => 'تاريخ الفاتورة',
            'distributor_id' => 'الموزع',
            'notes' => 'ملاحظات',
            'discount' => 'الخصم',
            'lines' => 'العناصر',
            'lines.*.product_id' => 'المنتج',
            'lines.*.quantity' => 'الكمية',
            'lines.*.unit_price' => 'سعر الوحدة',
        ];
    }
}
