<?php

namespace App\Http\Requests;

use App\Models\SalesInvoice;
use Illuminate\Foundation\Http\FormRequest;

class PostSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SalesInvoice|null $invoice */
        $invoice = $this->route('sales_invoice');

        return $invoice !== null
            && ($this->user()?->can('post', $invoice) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
