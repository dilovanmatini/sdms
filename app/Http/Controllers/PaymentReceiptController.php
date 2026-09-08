<?php

namespace App\Http\Controllers;

use App\Actions\PaymentReceipt\CancelAction;
use App\Actions\PaymentReceipt\CreateEditAction;
use App\Actions\PaymentReceipt\DestroyAction;
use App\Actions\PaymentReceipt\IndexAction;
use App\Actions\PaymentReceipt\PostAction;
use App\Actions\PaymentReceipt\PrintAction;
use App\Actions\PaymentReceipt\StoreUpdateAction;
use App\Http\Requests\CancelPaymentReceiptRequest;
use App\Http\Requests\PostPaymentReceiptRequest;
use App\Http\Requests\StoreUpdatePaymentReceiptRequest;
use App\Models\PaymentReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Response;

class PaymentReceiptController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', PaymentReceipt::class);

        return $action->handle($request);
    }

    public function createEdit(Request $request, ?PaymentReceipt $paymentReceipt, CreateEditAction $action): Response
    {
        if ($paymentReceipt?->exists) {
            $this->authorize('view', $paymentReceipt);
        } else {
            $this->authorize('create', PaymentReceipt::class);
        }

        return $action->handle($request, $paymentReceipt);
    }

    public function storeUpdate(StoreUpdatePaymentReceiptRequest $request, ?PaymentReceipt $paymentReceipt, StoreUpdateAction $action): RedirectResponse
    {
        return $action->handle($request, $paymentReceipt);
    }

    public function destroy(PaymentReceipt $paymentReceipt, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $paymentReceipt);

        return $action->handle($paymentReceipt);
    }

    public function post(PostPaymentReceiptRequest $request, PaymentReceipt $paymentReceipt, PostAction $action): RedirectResponse
    {
        return $action->handle($request, $paymentReceipt);
    }

    public function cancel(CancelPaymentReceiptRequest $request, PaymentReceipt $paymentReceipt, CancelAction $action): RedirectResponse
    {
        return $action->handle($request, $paymentReceipt);
    }

    public function print(PaymentReceipt $paymentReceipt, PrintAction $action): HttpResponse
    {
        $this->authorize('print', $paymentReceipt);

        return $action->handle($paymentReceipt);
    }
}
