import { Head, Link } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import {
    ChartColumn,
    FileSpreadsheet,
    Package,
    ScrollText,
    ShoppingCart,
    Truck,
    Users,
    Wallet,
} from 'lucide-react';
import type { ComponentType } from 'react';
import Heading from '@/components/heading';
import { toUrl } from '@/lib/utils';
import { index as reportsIndex, show as reportsShow } from '@/routes/reports';

type ReportCard = {
    type: string;
    title: string;
    description: string;
    uses_date_range: boolean;
    external_href?: string;
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
    'customer-statement': ScrollText,
};

export default function ReportsIndex({ reports }: Props) {
    return (
        <>
            <Head title="التقارير" />
            <div className="space-y-6">
                <Heading
                    title="التقارير"
                    description="تقارير المخزون والمبيعات والمدفوعات والذمم"
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {reports.map((report) => {
                        const Icon = icons[report.type] ?? ChartColumn;
                        const href = report.external_href
                            ? report.external_href
                            : toUrl(reportsShow(report.type));

                        return (
                            <Link
                                key={report.type}
                                href={href}
                                className="block rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                            >
                                <Card className="h-full transition hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <div className="flex items-start gap-3">
                                        <div className="rounded-lg bg-primary-50 p-2 text-primary-700 dark:bg-gray-700 dark:text-primary-300">
                                            <Icon className="h-5 w-5" />
                                        </div>
                                        <div className="space-y-1">
                                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                                {report.title}
                                            </h2>
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
            </div>
        </>
    );
}

ReportsIndex.layout = {
    breadcrumbs: [{ title: 'التقارير', href: reportsIndex() }],
};
