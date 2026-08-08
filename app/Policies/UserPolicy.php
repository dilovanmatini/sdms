<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageUsers);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasAbility(Ability::ManageUsers);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageUsers);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasAbility(Ability::ManageUsers);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasAbility(Ability::ManageUsers) && $user->id !== $model->id;
    }
}
