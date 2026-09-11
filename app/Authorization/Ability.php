<?php

namespace App\Authorization;

enum Ability: string
{
    case ManageUsers = 'manage_users';
    case ManageCategories = 'manage_categories';
    case ManageProducts = 'manage_products';
    case ManageSuppliers = 'manage_suppliers';
    case ManageDistributors = 'manage_distributors';
    case ManageUnits = 'manage_units';
    case ManageSettings = 'manage_settings';
    case ManagePurchases = 'manage_purchases';
    case ViewInventory = 'view_inventory';
    case ManageSales = 'manage_sales';
    case ManageOpeningBalances = 'manage_opening_balances';
    case ManageReceipts = 'manage_receipts';
    case ViewStatements = 'view_statements';
    case ViewReports = 'view_reports';
    case ViewDashboard = 'view_dashboard';
}
