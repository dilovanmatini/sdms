<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageProducts);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasAbility(Ability::ManageProducts);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageProducts);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasAbility(Ability::ManageProducts);
    }

    public function delete(User $user, Product $product): bool
    {
        if (! $user->hasAbility(Ability::ManageProducts)) {
            return false;
        }

        return ! $product->inventoryTransactions()->exists()
            && ! $product->purchaseLines()->exists()
            && ! $product->salesInvoiceLines()->exists();
    }
}
