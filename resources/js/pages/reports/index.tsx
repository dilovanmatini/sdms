import { Head, Link } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import {
    ChartColumn,
    FileSpreadsheet,
    Package,
    ShoppingCart,
    Truck,
    Users,
    Wallet,
} from 'lucide-react';
import type { ComponentType } from 'react';
import { FormCard } from '@/components/form-card';
import { toUrl } from '@/lib/utils';
import { index as reportsIndex, show as reportsShow } from '@/routes/reports';

type ReportCard = {
    type: string;
    title: string;
    description: string;
    uses_date_range: boolean;
};

type Props = {
    reports: ReportCard[];
};

const icons: Record<string, ComponentType<{ className?: string }>> = {
    inventory: Package,
    suppliers: Truck,
    purchases: ShoppingCart,
    sales: ChartColumn,
    'outstanding-customers': Users,
    payments: Wallet,
    'daily-sales': FileSpreadsheet,
    'monthly-sales': FileSpreadsheet,
};

export default function ReportsIndex({ reports }: Props) {
    return (
        <>
            <Head title="التقارير" />
            <FormCard
                title="التقارير"
                description="تقارير المخزون والمبيعات والمدفوعات والذمم"
                icon={ChartColumn}
            >
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {reports.map((report) => {
                        const Icon = icons[report.type] ?? ChartColumn;

                        return (
                            <Link
                                key={report.type}
                                href={toUrl(reportsShow(report.type))}
                                className="block rounded-lg focus:ring-0 focus:outline-none focus-visible:shadow-focus"
                            >
                                <Card className="h-full transition hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <div className="flex items-start gap-3">
                                        <div className="rounded-lg bg-primary-50 p-2 text-primary-700 dark:bg-gray-700 dark:text-primary-300">
                                            <Icon className="h-5 w-5" />
                                        </div>
                                        <div className="min-w-0 space-y-1">
                                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                                {report.title}
                                            </h3>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                                {report.description}
                                            </p>
                                        </div>
                                    </div>
                                </Card>
                            </Link>
                        );
                    })}
                </div>
            </FormCard>
        </>
    );
}

ReportsIndex.layout = {
    breadcrumbs: [{ title: 'التقارير', href: reportsIndex() }],
};
