<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManagePurchases);
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $user->hasAbility(Ability::ManagePurchases);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManagePurchases);
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $user->hasAbility(Ability::ManagePurchases) && ! $purchase->isPosted();
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        return $user->hasAbility(Ability::ManagePurchases) && ! $purchase->isPosted();
    }

    public function post(User $user, Purchase $purchase): bool
    {
        return $user->hasAbility(Ability::ManagePurchases) && ! $purchase->isPosted();
    }
}
