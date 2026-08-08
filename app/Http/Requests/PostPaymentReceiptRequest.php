<?php

namespace App\Http\Requests;

use App\Models\PaymentReceipt;
use Illuminate\Foundation\Http\FormRequest;

class PostPaymentReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PaymentReceipt|null $receipt */
        $receipt = $this->route('payment_receipt');

        return $receipt !== null
            && ($this->user()?->can('post', $receipt) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
