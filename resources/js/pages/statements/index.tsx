import { Head, router } from '@inertiajs/react';
import {
    Button,
    Label,
    Select,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
    TextInput,
} from 'flowbite-react';
import { FileDown, Printer, Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { PageHeader } from '@/components/page-header';
import {
    index as statementsIndex,
    pdf as statementsPdf,
    print as statementsPrint,
} from '@/routes/statements';

type DistributorOption = {
    id: number;
    name: string;
};

type StatementEntry = {
    id: number;
    entry_date: string;
    type: string;
    type_label: string;
    reference_number: string | null;
    debit: string;
    credit: string;
    running_balance: string;
};

type Statement = {
    distributor: {
        id: number;
        name: string;
        contact_person: string | null;
        phone: string | null;
        address: string | null;
    };
    from_date: string | null;
    to_date: string | null;
    opening_balance: string;
    closing_balance: string;
    total_debit: string;
    total_credit: string;
    entries: StatementEntry[];
};

type Props = {
    distributors: DistributorOption[];
    filters: {
        distributor_id: number | null;
        from_date: string | null;
        to_date: string | null;
    };
    statement: Statement | null;
};

export default function StatementsIndex({
    distributors,
    filters,
    statement,
}: Props) {
    const [distributorId, setDistributorId] = useState(
        filters.distributor_id ? String(filters.distributor_id) : '',
    );
    const [fromDate, setFromDate] = useState(filters.from_date ?? '');
    const [toDate, setToDate] = useState(filters.to_date ?? '');

    const query = {
        distributor_id: distributorId,
        ...(fromDate ? { from_date: fromDate } : {}),
        ...(toDate ? { to_date: toDate } : {}),
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        router.get(statementsIndex.url(), query, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <>
            <Head title="كشف حساب العميل" />
            <div className="space-y-6">
                <PageHeader
                    title="كشف حساب العميل"
                    description="عرض الحركات والرصيد الجاري والمتبقي للموزع"
                >
                    {statement && (
                        <>
                            <Button
                                color="light"
                                href={statementsPrint.url({ query })}
                                as="a"
                                target="_blank"
                                rel="noreferrer"
                            >
                                <Printer className="me-2 h-4 w-4" />
                                طباعة
                            </Button>
                            <Button
                                color="light"
                                href={statementsPdf.url({ query })}
                                as="a"
                            >
                                <FileDown className="me-2 h-4 w-4" />
                                PDF
                            </Button>
                        </>
                    )}
                </PageHeader>

                <form
                    onSubmit={submit}
                    className="grid gap-4 rounded-lg border border-gray-200 p-4 md:grid-cols-4 dark:border-gray-700"
                >
                    <div className="grid gap-2 md:col-span-2">
                        <Label htmlFor="distributor_id">الموزع</Label>
                        <Select
                            id="distributor_id"
                            value={distributorId}
                            onChange={(event) =>
                                setDistributorId(event.target.value)
                            }
                            required
                        >
                            <option value="">اختر الموزع</option>
                            {distributors.map((distributor) => (
                                <option
                                    key={distributor.id}
                                    value={distributor.id}
                                >
                                    {distributor.name}
                                </option>
                            ))}
                        </Select>
                    </div>

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
                            onChange={(event) => setToDate(event.target.value)}
                        />
                    </div>

                    <div className="md:col-span-4">
                        <Button type="submit">
                            <Search className="me-2 h-4 w-4" />
                            عرض الكشف
                        </Button>
                    </div>
                </form>

                {!statement && (
                    <p className="text-sm text-gray-500">
                        اختر الموزع والفترة ثم اضغط عرض الكشف.
                    </p>
                )}

                {statement && (
                    <div className="space-y-4">
                        <div className="grid gap-2 rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-700">
                            <div>
                                <span className="text-gray-500">الموزع: </span>
                                {statement.distributor.name}
                            </div>
                            <div>
                                <span className="text-gray-500">
                                    جهة الاتصال:{' '}
                                </span>
                                {statement.distributor.contact_person ?? '—'}
                            </div>
                            <div>
                                <span className="text-gray-500">الهاتف: </span>
                                {statement.distributor.phone ?? '—'}
                            </div>
                            <div>
                                <span className="text-gray-500">العنوان: </span>
                                {statement.distributor.address ?? '—'}
                            </div>
                        </div>

                        <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <Table>
                                <TableHead>
                                    <TableRow>
                                        <TableHeadCell>التاريخ</TableHeadCell>
                                        <TableHeadCell>النوع</TableHeadCell>
                                        <TableHeadCell>المرجع</TableHeadCell>
                                        <TableHeadCell>مدين</TableHeadCell>
                                        <TableHeadCell>دائن</TableHeadCell>
                                        <TableHeadCell>
                                            الرصيد الجاري
                                        </TableHeadCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody className="divide-y">
                                    <TableRow>
                                        <TableCell colSpan={3}>
                                            رصيد افتتاحي
                                        </TableCell>
                                        <TableCell>—</TableCell>
                                        <TableCell>—</TableCell>
                                        <TableCell className="font-medium tabular-nums">
                                            {statement.opening_balance}
                                        </TableCell>
                                    </TableRow>
                                    {statement.entries.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={6}
                                                className="text-center text-gray-500"
                                            >
                                                لا توجد حركات في هذه الفترة
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        statement.entries.map((entry) => (
                                            <TableRow key={entry.id}>
                                                <TableCell>
                                                    {entry.entry_date}
                                                </TableCell>
                                                <TableCell>
                                                    {entry.type_label}
                                                </TableCell>
                                                <TableCell>
                                                    {entry.reference_number ??
                                                        '—'}
                                                </TableCell>
                                                <TableCell className="tabular-nums">
                                                    {entry.debit !== '0.00'
                                                        ? entry.debit
                                                        : '—'}
                                                </TableCell>
                                                <TableCell className="tabular-nums">
                                                    {entry.credit !== '0.00'
                                                        ? entry.credit
                                                        : '—'}
                                                </TableCell>
                                                <TableCell className="font-medium tabular-nums">
                                                    {entry.running_balance}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        <div className="grid gap-2 rounded-lg border border-gray-200 p-4 text-sm sm:grid-cols-3 dark:border-gray-700">
                            <div>
                                <span className="text-gray-500">
                                    إجمالي المدين:{' '}
                                </span>
                                <span className="font-medium tabular-nums">
                                    {statement.total_debit}
                                </span>
                            </div>
                            <div>
                                <span className="text-gray-500">
                                    إجمالي الدائن:{' '}
                                </span>
                                <span className="font-medium tabular-nums">
                                    {statement.total_credit}
                                </span>
                            </div>
                            <div>
                                <span className="text-gray-500">
                                    الرصيد المتبقي:{' '}
                                </span>
                                <span className="font-semibold tabular-nums">
                                    {statement.closing_balance}
                                </span>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

StatementsIndex.layout = {
    breadcrumbs: [{ title: 'كشف حساب العميل', href: statementsIndex() }],
};
