<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\Distributor;
use App\Models\User;

class DistributorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageDistributors);
    }

    public function view(User $user, Distributor $distributor): bool
    {
        return $user->hasAbility(Ability::ManageDistributors);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageDistributors);
    }

    public function update(User $user, Distributor $distributor): bool
    {
        return $user->hasAbility(Ability::ManageDistributors);
    }

    public function delete(User $user, Distributor $distributor): bool
    {
        return $user->hasAbility(Ability::ManageDistributors)
            && ! $distributor->salesInvoices()->exists()
            && ! $distributor->paymentReceipts()->exists();
    }
}
