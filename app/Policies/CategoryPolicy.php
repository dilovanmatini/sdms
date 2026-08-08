<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageCategories);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasAbility(Ability::ManageCategories);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageCategories);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasAbility(Ability::ManageCategories);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasAbility(Ability::ManageCategories) && ! $category->products()->exists();
    }
}
