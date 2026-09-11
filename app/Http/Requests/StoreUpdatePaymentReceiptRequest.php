<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdatePaymentReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $receipt = $this->route('payment_receipt');

        if ($receipt?->exists) {
            return $this->user()?->can('update', $receipt) ?? false;
        }

        return $this->user()?->can('create', PaymentReceipt::class) ?? false;
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
        $receipt = $this->route('payment_receipt');

        $distributorRule = $receipt?->exists
            ? Rule::exists(Distributor::class, 'id')->where(function ($query) use ($receipt): void {
                $query->where('is_active', true)
                    ->orWhere('id', $receipt->distributor_id);
            })
            : Rule::exists(Distributor::class, 'id')->where('is_active', true);

        return [
            'receipt_date' => ['required', 'date'],
            'distributor_id' => ['required', 'integer', $distributorRule],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'receipt_date' => 'تاريخ السند',
            'distributor_id' => 'الموزع',
            'payment_method' => 'طريقة الدفع',
            'amount' => 'المبلغ',
            'notes' => 'ملاحظات',
        ];
    }
}
