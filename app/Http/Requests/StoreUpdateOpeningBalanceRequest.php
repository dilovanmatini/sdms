<?php

namespace App\Http\Requests;

use App\Models\Distributor;
use App\Models\OpeningBalance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateOpeningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $openingBalance = $this->route('opening_balance');

        if ($openingBalance?->exists) {
            return $this->user()?->can('update', $openingBalance) ?? false;
        }

        return $this->user()?->can('create', OpeningBalance::class) ?? false;
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
        $openingBalance = $this->route('opening_balance');

        $distributorRule = $openingBalance?->exists
            ? Rule::exists(Distributor::class, 'id')->where(function ($query) use ($openingBalance): void {
                $query->where('is_active', true)
                    ->orWhere('id', $openingBalance->distributor_id);
            })
            : Rule::exists(Distributor::class, 'id')->where('is_active', true);

        return [
            'entry_date' => ['required', 'date'],
            'distributor_id' => ['required', 'integer', $distributorRule],
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
            'entry_date' => 'تاريخ المبلغ',
            'distributor_id' => 'الموزع',
            'amount' => 'المبلغ',
            'notes' => 'ملاحظات',
        ];
    }
}
