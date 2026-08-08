<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\SalesInvoice;
use App\Models\User;

class SalesInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageSales);
    }

    public function view(User $user, SalesInvoice $salesInvoice): bool
    {
        return $user->hasAbility(Ability::ManageSales);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageSales);
    }

    public function update(User $user, SalesInvoice $salesInvoice): bool
    {
        return $user->hasAbility(Ability::ManageSales) && ! $salesInvoice->isPosted();
    }

    public function delete(User $user, SalesInvoice $salesInvoice): bool
    {
        return $user->hasAbility(Ability::ManageSales) && ! $salesInvoice->isPosted();
    }

    public function post(User $user, SalesInvoice $salesInvoice): bool
    {
        return $user->hasAbility(Ability::ManageSales) && ! $salesInvoice->isPosted();
    }
}
