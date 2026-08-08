<?php

namespace App\Http\Requests;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;

class PostPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Purchase|null $purchase */
        $purchase = $this->route('purchase');

        return $purchase !== null
            && ($this->user()?->can('post', $purchase) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
