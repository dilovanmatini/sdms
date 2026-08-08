import { Head } from '@inertiajs/react';
import {
    Card,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
} from 'flowbite-react';
import {
    Package,
    ShoppingCart,
    Users,
    Wallet,
} from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import { dashboard } from '@/routes';

type Metrics = {
    current_inventory_units: string;
    today_sales: string;
    month_sales: string;
    outstanding_receivables: string;
    total_customers: number;
    total_products: number;
    recent_sales: Array<{
        id: number;
        number: string;
        invoice_date: string | null;
        distributor: string | null;
        grand_total: string;
    }>;
    recent_payments: Array<{
        id: number;
        number: string;
        receipt_date: string | null;
        distributor: string | null;
        total_amount: string;
    }>;
};

type Props = {
    metrics: Metrics;
};

export default function Dashboard({ metrics }: Props) {
    const cards = [
        {
            title: 'المخزون الحالي',
            value: metrics.current_inventory_units,
            hint: 'إجمالي الوحدات',
            icon: Package,
        },
        {
            title: 'مبيعات اليوم',
            value: metrics.today_sales,
            hint: 'فواتير مرحّلة',
            icon: ShoppingCart,
        },
        {
            title: 'مبيعات الشهر',
            value: metrics.month_sales,
            hint: 'فواتير مرحّلة',
            icon: ShoppingCart,
        },
        {
            title: 'الذمم المستحقة',
            value: metrics.outstanding_receivables,
            hint: 'أرصدة العملاء',
            icon: Wallet,
        },
        {
            title: 'عدد العملاء',
            value: String(metrics.total_customers),
            hint: 'الموزعون',
            icon: Users,
        },
        {
            title: 'عدد المنتجات',
            value: String(metrics.total_products),
            hint: 'في النظام',
            icon: Package,
        },
    ];

    return (
        <>
            <Head title="الصفحة الرئيسية" />
            <div className="space-y-6">
                <PageHeader
                    title="الصفحة الرئيسية"
                    description="ملخص المخزون والمبيعات والذمم"
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {cards.map((card) => (
                        <Card key={card.title}>
                            <div className="flex items-start justify-between gap-3">
                                <div className="space-y-1">
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {card.title}
                                    </p>
                                    <p className="text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">
                                        {card.value}
                                    </p>
                                    <p className="text-xs text-gray-400">
                                        {card.hint}
                                    </p>
                                </div>
                                <div className="rounded-lg bg-primary-50 p-2 text-primary-700 dark:bg-gray-700 dark:text-primary-300">
                                    <card.icon className="h-5 w-5" />
                                </div>
                            </div>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                    <Card>
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            أحدث المبيعات
                        </h2>
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHead>
                                    <TableRow>
                                        <TableHeadCell>الرقم</TableHeadCell>
                                        <TableHeadCell>التاريخ</TableHeadCell>
                                        <TableHeadCell>الموزع</TableHeadCell>
                                        <TableHeadCell>الإجمالي</TableHeadCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody className="divide-y">
                                    {metrics.recent_sales.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={4}
                                                className="text-center text-gray-500"
                                            >
                                                لا توجد مبيعات حديثة
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        metrics.recent_sales.map((sale) => (
                                            <TableRow key={sale.id}>
                                                <TableCell className="font-medium">
                                                    {sale.number}
                                                </TableCell>
                                                <TableCell>
                                                    {sale.invoice_date ?? '—'}
                                                </TableCell>
                                                <TableCell>
                                                    {sale.distributor ?? '—'}
                                                </TableCell>
                                                <TableCell className="tabular-nums">
                                                    {sale.grand_total}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </Card>

                    <Card>
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            أحدث المدفوعات
                        </h2>
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHead>
                                    <TableRow>
                                        <TableHeadCell>الرقم</TableHeadCell>
                                        <TableHeadCell>التاريخ</TableHeadCell>
                                        <TableHeadCell>الموزع</TableHeadCell>
                                        <TableHeadCell>المبلغ</TableHeadCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody className="divide-y">
                                    {metrics.recent_payments.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={4}
                                                className="text-center text-gray-500"
                                            >
                                                لا توجد مدفوعات حديثة
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        metrics.recent_payments.map(
                                            (payment) => (
                                                <TableRow key={payment.id}>
                                                    <TableCell className="font-medium">
                                                        {payment.number}
                                                    </TableCell>
                                                    <TableCell>
                                                        {payment.receipt_date ??
                                                            '—'}
                                                    </TableCell>
                                                    <TableCell>
                                                        {payment.distributor ??
                                                            '—'}
                                                    </TableCell>
                                                    <TableCell className="tabular-nums">
                                                        {payment.total_amount}
                                                    </TableCell>
                                                </TableRow>
                                            ),
                                        )
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </Card>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'الصفحة الرئيسية', href: dashboard() }],
};
