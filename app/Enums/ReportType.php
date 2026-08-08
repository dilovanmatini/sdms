<?php

namespace App\Enums;

enum ReportType: string
{
    case Inventory = 'inventory';
    case Suppliers = 'suppliers';
    case Purchases = 'purchases';
    case Sales = 'sales';
    case OutstandingCustomers = 'outstanding-customers';
    case Payments = 'payments';
    case DailySales = 'daily-sales';
    case MonthlySales = 'monthly-sales';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => 'تقرير المخزون',
            self::Suppliers => 'تقرير الموردين',
            self::Purchases => 'تقرير المشتريات',
            self::Sales => 'تقرير المبيعات',
            self::OutstandingCustomers => 'العملاء ذوو الأرصدة',
            self::Payments => 'تقرير المدفوعات',
            self::DailySales => 'المبيعات اليومية',
            self::MonthlySales => 'المبيعات الشهرية',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Inventory => 'المنتجات والكميات المتاحة',
            self::Suppliers => 'الموردون ونشاط المشتريات',
            self::Purchases => 'فواتير المشتريات المرحّلة',
            self::Sales => 'فواتير المبيعات المرحّلة',
            self::OutstandingCustomers => 'الموزعون الذين عليهم أرصدة متبقية',
            self::Payments => 'سندات القبض المرحّلة',
            self::DailySales => 'إجمالي المبيعات حسب اليوم',
            self::MonthlySales => 'إجمالي المبيعات حسب الشهر',
        };
    }

    public function usesDateRange(): bool
    {
        return match ($this) {
            self::Inventory, self::Suppliers, self::OutstandingCustomers => false,
            default => true,
        };
    }
}
