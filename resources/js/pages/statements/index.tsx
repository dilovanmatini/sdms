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
import { FileDown, Printer, ScrollText, Search } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { AsyncSearchableSelect } from '@/components/async-searchable-select';
import type { SearchableSelectOption } from '@/components/async-searchable-select';
import { FormCard } from '@/components/form-card';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { useFormatMoney } from '@/lib/money';
import { distributors as distributorLookups } from '@/routes/lookups';
import {
    index as statementsIndex,
    pdf as statementsPdf,
    print as statementsPrint,
} from '@/routes/statements';

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
    selected_distributor: SearchableSelectOption | null;
    filters: {
        distributor_id: number | null;
        from_date: string | null;
        to_date: string | null;
    };
    statement: Statement | null;
};

export default function StatementsIndex({
    selected_distributor,
    filters,
    statement,
}: Props) {
    const formatMoney = useFormatMoney();
    const zeroMoney = formatMoney('0.00');
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
            <FormCard
                title="كشف حساب العميل"
                description="عرض الحركات والرصيد الجاري والمتبقي للموزع"
                icon={ScrollText}
                actions={
                    statement ? (
                        <>
                            <Button
                                color="light"
                                href={statementsPrint.url({ query })}
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
                                href={statementsPdf.url({ query })}
                                as="a"
                                className="inline-flex items-center gap-2"
                            >
                                <FileDown className="h-4 w-4" />
                                PDF
                            </Button>
                        </>
                    ) : undefined
                }
            >
                <div className="space-y-6">
                    <form
                        onSubmit={submit}
                        className="grid gap-4 rounded-lg border border-gray-200 p-4 md:grid-cols-4 dark:border-gray-700"
                    >
                        <div className="grid gap-2 md:col-span-2">
                            <Label htmlFor="distributor_id">الموزع</Label>
                            <AsyncSearchableSelect
                                id="distributor_id"
                                name="distributor_id"
                                required
                                placeholder="اختر الموزع"
                                searchPlaceholder="ابحث عن موزع..."
                                value={distributorId}
                                initialOptions={
                                    selected_distributor
                                        ? [selected_distributor]
                                        : []
                                }
                                buildUrl={(search) =>
                                    distributorLookups.url(
                                        lookupQuery(search, {
                                            active_only: 0,
                                            include: distributorId || undefined,
                                        }),
                                    )
                                }
                                onChange={setDistributorId}
                            />
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
                                onChange={(event) =>
                                    setToDate(event.target.value)
                                }
                            />
                        </div>

                        <div className="md:col-span-4">
                            <Button
                                type="submit"
                                className="inline-flex items-center gap-2"
                            >
                                <Search className="h-4 w-4" />
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
                                    <span className="text-gray-500">
                                        الموزع:{' '}
                                    </span>
                                    {statement.distributor.name}
                                </div>
                                <div>
                                    <span className="text-gray-500">
                                        جهة الاتصال:{' '}
                                    </span>
                                    {statement.distributor.contact_person ??
                                        '—'}
                                </div>
                                <div>
                                    <span className="text-gray-500">
                                        الهاتف:{' '}
                                    </span>
                                    {statement.distributor.phone ?? '—'}
                                </div>
                                <div>
                                    <span className="text-gray-500">
                                        العنوان:{' '}
                                    </span>
                                    {statement.distributor.address ?? '—'}
                                </div>
                            </div>

                            <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <Table>
                                    <TableHead>
                                        <TableRow>
                                            <TableHeadCell className="text-start">
                                                التاريخ
                                            </TableHeadCell>
                                            <TableHeadCell className="text-start">
                                                النوع
                                            </TableHeadCell>
                                            <TableHeadCell className="text-start">
                                                المرجع
                                            </TableHeadCell>
                                            <TableHeadCell className="text-end">
                                                مدين
                                            </TableHeadCell>
                                            <TableHeadCell className="text-end">
                                                دائن
                                            </TableHeadCell>
                                            <TableHeadCell className="text-end">
                                                الرصيد الجاري
                                            </TableHeadCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
                                        <TableRow>
                                            <TableCell
                                                colSpan={3}
                                                className="text-start"
                                            >
                                                رصيد افتتاحي
                                            </TableCell>
                                            <TableCell className="text-end">
                                                —
                                            </TableCell>
                                            <TableCell className="text-end">
                                                —
                                            </TableCell>
                                            <TableCell className="text-end font-medium tabular-nums">
                                                {statement.opening_balance}
                                            </TableCell>
                                        </TableRow>
                                        {statement.entries.length === 0 ? (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={6}
                                                    className="py-8 text-center text-gray-500"
                                                >
                                                    لا توجد حركات في هذه الفترة
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            statement.entries.map((entry) => (
                                                <TableRow key={entry.id}>
                                                    <TableCell className="text-start">
                                                        {entry.entry_date}
                                                    </TableCell>
                                                    <TableCell className="text-start">
                                                        {entry.type_label}
                                                    </TableCell>
                                                    <TableCell className="text-start">
                                                        {entry.reference_number ??
                                                            '—'}
                                                    </TableCell>
                                                    <TableCell className="text-end tabular-nums">
                                                        {entry.debit !==
                                                        zeroMoney
                                                            ? entry.debit
                                                            : '—'}
                                                    </TableCell>
                                                    <TableCell className="text-end tabular-nums">
                                                        {entry.credit !==
                                                        zeroMoney
                                                            ? entry.credit
                                                            : '—'}
                                                    </TableCell>
                                                    <TableCell className="text-end font-medium tabular-nums">
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
            </FormCard>
        </>
    );
}

StatementsIndex.layout = {
    breadcrumbs: [{ title: 'كشف حساب العميل', href: statementsIndex() }],
};
