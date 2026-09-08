<?php

namespace App\Http\Controllers;

use App\Actions\SalesInvoice\CancelAction;
use App\Actions\SalesInvoice\CreateEditAction;
use App\Actions\SalesInvoice\DestroyAction;
use App\Actions\SalesInvoice\IndexAction;
use App\Actions\SalesInvoice\PostAction;
use App\Actions\SalesInvoice\PrintAction;
use App\Actions\SalesInvoice\StoreUpdateAction;
use App\Http\Requests\CancelSalesInvoiceRequest;
use App\Http\Requests\PostSalesInvoiceRequest;
use App\Http\Requests\StoreUpdateSalesInvoiceRequest;
use App\Models\SalesInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Response;

class SalesInvoiceController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', SalesInvoice::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?SalesInvoice $salesInvoice, CreateEditAction $action): Response
    {
        if ($salesInvoice?->exists) {
            $this->authorize('view', $salesInvoice);
        } else {
            $this->authorize('create', SalesInvoice::class);
        }

        return $action->handle($request, $salesInvoice);
    }

    public function storeUpdate(StoreUpdateSalesInvoiceRequest $request, ?SalesInvoice $salesInvoice, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $salesInvoice);
    }

    public function destroy(SalesInvoice $salesInvoice, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $salesInvoice);

        return $action->handle($salesInvoice);
    }

    public function post(PostSalesInvoiceRequest $request, SalesInvoice $salesInvoice, PostAction $action): RedirectResponse
    {
        return $action->handle($request, $salesInvoice);
    }

    public function cancel(CancelSalesInvoiceRequest $request, SalesInvoice $salesInvoice, CancelAction $action): RedirectResponse
    {
        return $action->handle($request, $salesInvoice);
    }

    public function print(SalesInvoice $salesInvoice, PrintAction $action): HttpResponse
    {
        $this->authorize('print', $salesInvoice);

        return $action->handle($salesInvoice);
    }
}
