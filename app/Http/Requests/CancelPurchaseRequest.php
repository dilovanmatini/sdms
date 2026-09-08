<?php

namespace App\Http\Requests;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;

class CancelPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Purchase|null $purchase */
        $purchase = $this->route('purchase');

        return $purchase !== null
            && ($this->user()?->can('cancel', $purchase) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
