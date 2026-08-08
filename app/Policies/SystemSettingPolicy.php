<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\SystemSetting;
use App\Models\User;

class SystemSettingPolicy
{
    public function view(User $user, SystemSetting $systemSetting): bool
    {
        return $user->hasAbility(Ability::ManageSettings);
    }

    public function update(User $user, SystemSetting $systemSetting): bool
    {
        return $user->hasAbility(Ability::ManageSettings);
    }
}
