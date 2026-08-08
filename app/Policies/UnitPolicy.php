<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageUnits);
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->hasAbility(Ability::ManageUnits);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageUnits);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->hasAbility(Ability::ManageUnits);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->hasAbility(Ability::ManageUnits) && ! $unit->products()->exists();
    }
}
