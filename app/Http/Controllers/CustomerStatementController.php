<?php

namespace App\Http\Controllers;

use App\Actions\CustomerStatement\IndexAction;
use App\Actions\CustomerStatement\PdfAction;
use App\Actions\CustomerStatement\PrintAction;
use App\Authorization\Ability;
use App\Http\Requests\ShowCustomerStatementRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CustomerStatementController extends Controller
{
    public function index(Request $request, IndexAction $action): InertiaResponse
    {
        Gate::authorize(Ability::ViewStatements->value);

        return $action->handle($request);
    }

    public function print(ShowCustomerStatementRequest $request, PrintAction $action): Response
    {
        return $action->handle($request);
    }

    public function pdf(ShowCustomerStatementRequest $request, PdfAction $action): SymfonyResponse
    {
        return $action->handle($request);
    }
}
