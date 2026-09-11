<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\OpeningBalance;
use App\Models\User;

class OpeningBalancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageOpeningBalances);
    }

    public function view(User $user, OpeningBalance $openingBalance): bool
    {
        return $user->hasAbility(Ability::ManageOpeningBalances);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageOpeningBalances);
    }

    public function update(User $user, OpeningBalance $openingBalance): bool
    {
        return $user->hasAbility(Ability::ManageOpeningBalances) && $openingBalance->isDraft();
    }

    public function delete(User $user, OpeningBalance $openingBalance): bool
    {
        return $user->hasAbility(Ability::ManageOpeningBalances) && $openingBalance->isDraft();
    }

    public function post(User $user, OpeningBalance $openingBalance): bool
    {
        return $user->hasAbility(Ability::ManageOpeningBalances) && $openingBalance->isDraft();
    }

    public function cancel(User $user, OpeningBalance $openingBalance): bool
    {
        return $user->hasAbility(Ability::ManageOpeningBalances) && $openingBalance->isPosted();
    }
}
