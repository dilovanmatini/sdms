import { Head, Link } from '@inertiajs/react';
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
    LayoutDashboard,
    Package,
    ShoppingCart,
    Users,
    Wallet,
} from 'lucide-react';
import { FormCard } from '@/components/form-card';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import { createEdit as paymentReceiptsCreateEdit } from '@/routes/payment-receipts';
import { createEdit as salesInvoicesCreateEdit } from '@/routes/sales-invoices';

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
            iconClass:
                'bg-teal-50 text-teal-700 dark:bg-gray-700 dark:text-teal-300',
        },
        {
            title: 'مبيعات اليوم',
            value: metrics.today_sales,
            hint: 'فواتير نشطة',
            icon: ShoppingCart,
            iconClass:
                'bg-green-50 text-green-700 dark:bg-gray-700 dark:text-green-300',
        },
        {
            title: 'مبيعات الشهر',
            value: metrics.month_sales,
            hint: 'فواتير نشطة',
            icon: ShoppingCart,
            iconClass:
                'bg-blue-50 text-blue-700 dark:bg-gray-700 dark:text-blue-300',
        },
        {
            title: 'الذمم المستحقة',
            value: metrics.outstanding_receivables,
            hint: 'أرصدة العملاء',
            icon: Wallet,
            iconClass:
                'bg-amber-50 text-amber-700 dark:bg-gray-700 dark:text-amber-300',
        },
        {
            title: 'عدد العملاء',
            value: String(metrics.total_customers),
            hint: 'الموزعون',
            icon: Users,
            iconClass:
                'bg-purple-50 text-purple-700 dark:bg-gray-700 dark:text-purple-300',
        },
        {
            title: 'عدد المنتجات',
            value: String(metrics.total_products),
            hint: 'في النظام',
            icon: Package,
            iconClass:
                'bg-rose-50 text-rose-700 dark:bg-gray-700 dark:text-rose-300',
        },
    ];

    return (
        <>
            <Head title="الصفحة الرئيسية" />
            <FormCard
                title="الصفحة الرئيسية"
                description="ملخص المخزون والمبيعات والذمم"
                icon={LayoutDashboard}
            >
                <div className="space-y-6">
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        {cards.map((card) => (
                            <Card key={card.title}>
                                <div className="flex items-start justify-between gap-3">
                                    <div className="space-y-1">
                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                            {card.title}
                                        </p>
                                        <p className="text-2xl font-semibold text-gray-900 tabular-nums dark:text-white">
                                            {card.value}
                                        </p>
                                        <p className="text-xs text-gray-400">
                                            {card.hint}
                                        </p>
                                    </div>
                                    <div
                                        className={`rounded-lg p-2 ${card.iconClass}`}
                                    >
                                        <card.icon className="h-5 w-5" />
                                    </div>
                                </div>
                            </Card>
                        ))}
                    </div>

                    <div className="grid gap-4 xl:grid-cols-2">
                        <Card
                            className="h-full"
                            theme={{
                                root: {
                                    children:
                                        'flex h-full flex-col justify-start gap-4 p-6',
                                },
                            }}
                        >
                            <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                                أحدث المبيعات
                            </h3>
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHead>
                                        <TableRow>
                                            <TableHeadCell className="text-start">
                                                الرقم
                                            </TableHeadCell>
                                            <TableHeadCell className="text-start">
                                                التاريخ
                                            </TableHeadCell>
                                            <TableHeadCell className="text-start">
                                                الموزع
                                            </TableHeadCell>
                                            <TableHeadCell className="text-end">
                                                الإجمالي
                                            </TableHeadCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
                                        {metrics.recent_sales.length === 0 ? (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={4}
                                                    className="py-8 text-center text-gray-500"
                                                >
                                                    لا توجد مبيعات حديثة
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            metrics.recent_sales.map((sale) => (
                                                <TableRow key={sale.id}>
                                                    <TableCell className="text-start font-medium">
                                                        <Link
                                                            href={toUrl(
                                                                salesInvoicesCreateEdit(
                                                                    sale.id,
                                                                ),
                                                            )}
                                                            className="text-primary-700 hover:underline dark:text-primary-400"
                                                        >
                                                            {sale.number}
                                                        </Link>
                                                    </TableCell>
                                                    <TableCell className="text-start">
                                                        {sale.invoice_date ??
                                                            '—'}
                                                    </TableCell>
                                                    <TableCell className="text-start">
                                                        {sale.distributor ??
                                                            '—'}
                                                    </TableCell>
                                                    <TableCell className="text-end tabular-nums">
                                                        {sale.grand_total}
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </Card>

                        <Card
                            className="h-full"
                            theme={{
                                root: {
                                    children:
                                        'flex h-full flex-col justify-start gap-4 p-6',
                                },
                            }}
                        >
                            <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                                أحدث المدفوعات
                            </h3>
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHead>
                                        <TableRow>
                                            <TableHeadCell className="text-start">
                                                الرقم
                                            </TableHeadCell>
                                            <TableHeadCell className="text-start">
                                                التاريخ
                                            </TableHeadCell>
                                            <TableHeadCell className="text-start">
                                                الموزع
                                            </TableHeadCell>
                                            <TableHeadCell className="text-end">
                                                المبلغ
                                            </TableHeadCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
                                        {metrics.recent_payments.length ===
                                        0 ? (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={4}
                                                    className="py-8 text-center text-gray-500"
                                                >
                                                    لا توجد مدفوعات حديثة
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            metrics.recent_payments.map(
                                                (payment) => (
                                                    <TableRow key={payment.id}>
                                                        <TableCell className="text-start font-medium">
                                                            <Link
                                                                href={toUrl(
                                                                    paymentReceiptsCreateEdit(
                                                                        payment.id,
                                                                    ),
                                                                )}
                                                                className="text-primary-700 hover:underline dark:text-primary-400"
                                                            >
                                                                {payment.number}
                                                            </Link>
                                                        </TableCell>
                                                        <TableCell className="text-start">
                                                            {payment.receipt_date ??
                                                                '—'}
                                                        </TableCell>
                                                        <TableCell className="text-start">
                                                            {payment.distributor ??
                                                                '—'}
                                                        </TableCell>
                                                        <TableCell className="text-end tabular-nums">
                                                            {
                                                                payment.total_amount
                                                            }
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
            </FormCard>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'الصفحة الرئيسية', href: dashboard() }],
};
