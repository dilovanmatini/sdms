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
import { FileDown, FileSpreadsheet, Printer, Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { PageHeader } from '@/components/page-header';
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

    return (
        <>
            <Head title={report.title} />
            <div className="space-y-6">
                <PageHeader
                    title={report.title}
                    description={report.description}
                >
                    <Button
                        color="light"
                        href={reportsPrint.url(report.type, { query })}
                        as="a"
                        target="_blank"
                        rel="noreferrer"
                    >
                        <Printer className="me-2 h-4 w-4" />
                        طباعة
                    </Button>
                    <Button
                        color="light"
                        href={reportsPdf.url(report.type, { query })}
                        as="a"
                    >
                        <FileDown className="me-2 h-4 w-4" />
                        PDF
                    </Button>
                    <Button
                        color="light"
                        href={reportsExcel.url(report.type, { query })}
                        as="a"
                    >
                        <FileSpreadsheet className="me-2 h-4 w-4" />
                        Excel
                    </Button>
                </PageHeader>

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
                            <Button type="submit">
                                <Search className="me-2 h-4 w-4" />
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
                                    <TableHeadCell key={column.key}>
                                        {column.label}
                                    </TableHeadCell>
                                ))}
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {report.rows.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={report.columns.length}
                                        className="text-center text-gray-500"
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
                                                className="tabular-nums"
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
        </>
    );
}

ReportsShow.layout = {
    breadcrumbs: [
        { title: 'التقارير', href: reportsIndex() },
        { title: 'عرض', href: reportsShow('inventory') },
    ],
};
