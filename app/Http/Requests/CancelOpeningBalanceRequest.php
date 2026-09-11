<?php

namespace App\Http\Requests;

use App\Models\OpeningBalance;
use Illuminate\Foundation\Http\FormRequest;

class CancelOpeningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var OpeningBalance|null $openingBalance */
        $openingBalance = $this->route('opening_balance');

        return $openingBalance !== null
            && ($this->user()?->can('cancel', $openingBalance) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
