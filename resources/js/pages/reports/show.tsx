import { Head, router } from '@inertiajs/react';
import {
    Button,
    Label,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
    TextInput,
} from 'flowbite-react';
import {
    ChartColumn,
    FileDown,
    FileSpreadsheet,
    Package,
    Printer,
    Search,
    ShoppingCart,
    Truck,
    Users,
    Wallet,
} from 'lucide-react';
import type { ComponentType, FormEvent } from 'react';
import { useState } from 'react';
import { FormCard } from '@/components/form-card';
import {
    excel as reportsExcel,
    index as reportsIndex,
    pdf as reportsPdf,
    print as reportsPrint,
    show as reportsShow,
} from '@/routes/reports';

type ReportColumn = {
    key: string;
    label: string;
};

type ReportPayload = {
    type: string;
    title: string;
    description: string;
    from_date: string | null;
    to_date: string | null;
    columns: ReportColumn[];
    rows: Array<Record<string, string | null>>;
    meta: { row_count: number };
};

type Props = {
    report: ReportPayload;
    filters: {
        from_date: string | null;
        to_date: string | null;
    };
    uses_date_range: boolean;
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

export default function ReportsShow({
    report,
    filters,
    uses_date_range,
}: Props) {
    const [fromDate, setFromDate] = useState(filters.from_date ?? '');
    const [toDate, setToDate] = useState(filters.to_date ?? '');

    const query = {
        ...(fromDate ? { from_date: fromDate } : {}),
        ...(toDate ? { to_date: toDate } : {}),
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(reportsShow.url(report.type, { query }), {}, {
            preserveState: true,
            replace: true,
        });
    };

    const Icon = icons[report.type] ?? ChartColumn;

    return (
        <>
            <Head title={report.title} />
            <FormCard
                title={report.title}
                description={report.description}
                icon={Icon}
                actions={
                    <>
                        <Button
                            color="light"
                            href={reportsPrint.url(report.type, { query })}
                            as="a"
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center gap-2"
                        >
                            <Printer className="h-4 w-4" />
                            طباعة
                        </Button>
                        <Button
                            color="light"
                            href={reportsPdf.url(report.type, { query })}
                            as="a"
                            className="inline-flex items-center gap-2"
                        >
                            <FileDown className="h-4 w-4" />
                            PDF
                        </Button>
                        <Button
                            color="light"
                            href={reportsExcel.url(report.type, { query })}
                            as="a"
                            className="inline-flex items-center gap-2"
                        >
                            <FileSpreadsheet className="h-4 w-4" />
                            Excel
                        </Button>
                    </>
                }
            >
                <div className="space-y-4">
                    {uses_date_range && (
                        <form
                            onSubmit={submit}
                            className="grid gap-4 rounded-lg border border-gray-200 p-4 sm:grid-cols-3 dark:border-gray-700"
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="from_date">من تاريخ</Label>
                                <TextInput
                                    id="from_date"
                                    type="date"
                                    value={fromDate}
                                    onChange={(event) =>
                                        setFromDate(event.target.value)
                                    }
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="to_date">إلى تاريخ</Label>
                                <TextInput
                                    id="to_date"
                                    type="date"
                                    value={toDate}
                                    onChange={(event) =>
                                        setToDate(event.target.value)
                                    }
                                />
                            </div>
                            <div className="flex items-end">
                                <Button
                                    type="submit"
                                    className="inline-flex items-center gap-2"
                                >
                                    <Search className="h-4 w-4" />
                                    تطبيق
                                </Button>
                            </div>
                        </form>
                    )}

                    <p className="text-sm text-gray-500">
                        عدد الصفوف: {report.meta.row_count}
                    </p>

                    <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <Table>
                            <TableHead>
                                <TableRow>
                                    {report.columns.map((column) => (
                                        <TableHeadCell
                                            key={column.key}
                                            className="text-start"
                                        >
                                            {column.label}
                                        </TableHeadCell>
                                    ))}
                                </TableRow>
                            </TableHead>
                            <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {report.rows.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={report.columns.length}
                                            className="py-10 text-center text-gray-500"
                                        >
                                            لا توجد بيانات
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    report.rows.map((row, index) => (
                                        <TableRow key={index}>
                                            {report.columns.map((column) => (
                                                <TableCell
                                                    key={column.key}
                                                    className="text-start tabular-nums"
                                                >
                                                    {row[column.key] ?? '—'}
                                                </TableCell>
                                            ))}
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </FormCard>
        </>
    );
}

ReportsShow.layout = {
    breadcrumbs: [
        { title: 'التقارير', href: reportsIndex() },
        { title: 'عرض', href: reportsShow('inventory') },
    ],
};
