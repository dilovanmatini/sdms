<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageSuppliers);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->hasAbility(Ability::ManageSuppliers);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageSuppliers);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasAbility(Ability::ManageSuppliers);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasAbility(Ability::ManageSuppliers) && ! $supplier->purchases()->exists();
    }
}
