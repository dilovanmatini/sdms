<?php

namespace App\Actions\SalesInvoice;

use App\Http\Requests\PostSalesInvoiceRequest;
use App\Models\SalesInvoice;
use App\Services\SalesInvoicePoster;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class PostAction
{
    public function __construct(private SalesInvoicePoster $poster) {}

    public function handle(PostSalesInvoiceRequest $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        try {
            $this->poster->post($salesInvoice, $request->user());
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم التأكيد النهائي لفاتورة المبيعات بنجاح.']);

        return to_route('sales-invoices.index');
    }
}
