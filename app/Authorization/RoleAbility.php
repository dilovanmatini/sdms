<?php

namespace App\Authorization;

use App\Enums\UserRole;

class RoleAbility
{
    /**
     * @return list<Ability>
     */
    public static function for(UserRole $role): array
    {
        return match ($role) {
            UserRole::Administrator => Ability::cases(),
            UserRole::Warehouse => [
                Ability::ViewDashboard,
                Ability::ManageCategories,
                Ability::ManageProducts,
                Ability::ManageSuppliers,
                Ability::ManagePurchases,
                Ability::ViewInventory,
            ],
            UserRole::Sales => [
                Ability::ViewDashboard,
                Ability::ManageDistributors,
                Ability::ManageSales,
                Ability::ManageOpeningBalances,
                Ability::ViewInventory,
            ],
            UserRole::Accountant => [
                Ability::ViewDashboard,
                Ability::ManageReceipts,
                Ability::ManageOpeningBalances,
                Ability::ViewStatements,
                Ability::ViewReports,
                Ability::ManageDistributors,
            ],
            UserRole::Manager => [
                Ability::ViewDashboard,
                Ability::ManageUnits,
                Ability::ManageCategories,
                Ability::ManageProducts,
                Ability::ManageSuppliers,
                Ability::ManageDistributors,
                Ability::ManagePurchases,
                Ability::ViewInventory,
                Ability::ManageSales,
                Ability::ManageOpeningBalances,
                Ability::ManageReceipts,
                Ability::ViewStatements,
                Ability::ViewReports,
            ],
        };
    }

    public static function allows(UserRole $role, Ability|string $ability): bool
    {
        $ability = $ability instanceof Ability ? $ability : Ability::from($ability);

        return in_array($ability, self::for($role), true);
    }

    /**
     * @return list<string>
     */
    public static function values(UserRole $role): array
    {
        return array_map(
            static fn (Ability $ability): string => $ability->value,
            self::for($role),
        );
    }
}
