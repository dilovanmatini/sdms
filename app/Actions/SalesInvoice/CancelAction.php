<?php

namespace App\Actions\SalesInvoice;

use App\Http\Requests\CancelSalesInvoiceRequest;
use App\Models\SalesInvoice;
use App\Services\SalesInvoiceCanceller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use InvalidArgumentException;

class CancelAction
{
    public function __construct(private SalesInvoiceCanceller $canceller) {}

    public function handle(CancelSalesInvoiceRequest $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        try {
            $this->canceller->cancel($salesInvoice);
        } catch (InvalidArgumentException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إلغاء الفاتورة بنجاح.']);

        return to_route('sales-invoices.index');
    }
}
