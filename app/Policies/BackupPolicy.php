<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\Backup;
use App\Models\User;

class BackupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageSettings);
    }

    public function view(User $user, Backup $backup): bool
    {
        return $user->hasAbility(Ability::ManageSettings);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageSettings);
    }

    public function download(User $user, Backup $backup): bool
    {
        return $user->hasAbility(Ability::ManageSettings);
    }

    public function delete(User $user, Backup $backup): bool
    {
        return $user->hasAbility(Ability::ManageSettings) && $backup->canBeDeleted();
    }
}
