<?php

namespace App\Http\Controllers;

use App\Actions\Lookup\SearchCategoriesAction;
use App\Actions\Lookup\SearchDistributorsAction;
use App\Actions\Lookup\SearchOpenInvoicesAction;
use App\Actions\Lookup\SearchProductsAction;
use App\Actions\Lookup\SearchSuppliersAction;
use App\Actions\Lookup\SearchUnitsAction;
use App\Authorization\Ability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function products(Request $request, SearchProductsAction $action): JsonResponse
    {
        $this->authorizeLookup([
            Ability::ManageProducts,
            Ability::ManagePurchases,
            Ability::ManageSales,
            Ability::ViewInventory,
        ]);

        return $action->handle($request);
    }

    public function distributors(Request $request, SearchDistributorsAction $action): JsonResponse
    {
        $this->authorizeLookup([
            Ability::ManageDistributors,
            Ability::ManageSales,
            Ability::ManageReceipts,
            Ability::ViewStatements,
        ]);

        return $action->handle($request);
    }

    public function suppliers(Request $request, SearchSuppliersAction $action): JsonResponse
    {
        $this->authorizeLookup([
            Ability::ManageSuppliers,
            Ability::ManagePurchases,
        ]);

        return $action->handle($request);
    }

    public function categories(Request $request, SearchCategoriesAction $action): JsonResponse
    {
        $this->authorizeLookup([
            Ability::ManageCategories,
            Ability::ManageProducts,
            Ability::ViewInventory,
        ]);

        return $action->handle($request);
    }

    public function units(Request $request, SearchUnitsAction $action): JsonResponse
    {
        $this->authorizeLookup([
            Ability::ManageUnits,
            Ability::ManageProducts,
        ]);

        return $action->handle($request);
    }

    public function openInvoices(Request $request, SearchOpenInvoicesAction $action): JsonResponse
    {
        $this->authorizeLookup([
            Ability::ManageReceipts,
        ]);

        return $action->handle($request);
    }

    /**
     * @param  list<Ability>  $abilities
     */
    private function authorizeLookup(array $abilities): void
    {
        $user = request()->user();

        foreach ($abilities as $ability) {
            if ($user?->can($ability->value)) {
                return;
            }
        }

        abort(403);
    }
}
