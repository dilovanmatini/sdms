<?php

namespace App\Http\Requests;

use App\Enums\DocumentStatus;
use App\Enums\PaymentMethod;
use App\Models\Distributor;
use App\Models\PaymentReceipt;
use App\Models\SalesInvoice;
use App\Support\MoneyDisplay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.sales_invoice_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(SalesInvoice::class, 'id')->where('status', DocumentStatus::Posted->value),
            ],
            'allocations.*.amount' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $distributorId = (int) $this->input('distributor_id');

            foreach ($this->input('allocations', []) as $index => $allocation) {
                /** @var SalesInvoice|null $invoice */
                $invoice = SalesInvoice::query()->find($allocation['sales_invoice_id']);

                if ($invoice === null) {
                    continue;
                }

                if ((int) $invoice->distributor_id !== $distributorId) {
                    $validator->errors()->add(
                        "allocations.{$index}.sales_invoice_id",
                        'الفاتورة لا تتبع الموزع المحدد.',
                    );

                    continue;
                }

                $remaining = $invoice->remainingAmount();

                if (bccomp((string) $allocation['amount'], $remaining, 2) === 1) {
                    $validator->errors()->add(
                        "allocations.{$index}.amount",
                        'المبلغ يتجاوز المتبقي ('.MoneyDisplay::withSymbol($remaining).').',
                    );
                }
            }
        });
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
            'notes' => 'ملاحظات',
            'allocations' => 'التوزيعات',
            'allocations.*.sales_invoice_id' => 'الفاتورة',
            'allocations.*.amount' => 'المبلغ',
        ];
    }
}
