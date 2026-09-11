<?php

namespace App\Support;

use App\Models\SystemSetting;
use App\Models\User;

final class DashboardNumbersVisibility
{
    /**
     * Resolve whether dashboard figures should be shown for the user.
     *
     * A stored user preference wins; otherwise the general settings default applies.
     */
    public static function for(User $user): bool
    {
        if ($user->show_dashboard_numbers !== null) {
            return $user->show_dashboard_numbers;
        }

        return SystemSetting::current()->show_dashboard_numbers;
    }
}
