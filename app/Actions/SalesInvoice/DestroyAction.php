<?php

namespace App\Actions\SalesInvoice;

use App\Models\SalesInvoice;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(SalesInvoice $salesInvoice): RedirectResponse
    {
        $salesInvoice->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف فاتورة المبيعات بنجاح.']);

        return to_route('sales-invoices.index');
    }
}
