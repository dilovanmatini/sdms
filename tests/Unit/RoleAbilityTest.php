<?php

use App\Authorization\Ability;
use App\Authorization\RoleAbility;
use App\Enums\UserRole;

test('administrator has all abilities', function () {
    expect(RoleAbility::for(UserRole::Administrator))->toBe(Ability::cases());
});

test('warehouse role can manage purchases but not receipts', function () {
    expect(RoleAbility::allows(UserRole::Warehouse, Ability::ManagePurchases))->toBeTrue()
        ->and(RoleAbility::allows(UserRole::Warehouse, Ability::ManageReceipts))->toBeFalse();
});

test('sales role can manage invoices but not purchases', function () {
    expect(RoleAbility::allows(UserRole::Sales, Ability::ManageSales))->toBeTrue()
        ->and(RoleAbility::allows(UserRole::Sales, Ability::ManagePurchases))->toBeFalse();
});

test('manager can manage units but warehouse cannot', function () {
    expect(RoleAbility::allows(UserRole::Manager, Ability::ManageUnits))->toBeTrue()
        ->and(RoleAbility::allows(UserRole::Warehouse, Ability::ManageUnits))->toBeFalse();
});

test('only administrator can manage settings', function () {
    expect(RoleAbility::allows(UserRole::Administrator, Ability::ManageSettings))->toBeTrue()
        ->and(RoleAbility::allows(UserRole::Manager, Ability::ManageSettings))->toBeFalse()
        ->and(RoleAbility::allows(UserRole::Warehouse, Ability::ManageSettings))->toBeFalse();
});
