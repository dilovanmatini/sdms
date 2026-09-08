<?php

namespace App\Http\Requests;

use App\Models\SalesInvoice;
use Illuminate\Foundation\Http\FormRequest;

class CancelSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SalesInvoice|null $invoice */
        $invoice = $this->route('sales_invoice');

        return $invoice !== null
            && ($this->user()?->can('cancel', $invoice) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
