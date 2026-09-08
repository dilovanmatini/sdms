import { Head, Link, router } from '@inertiajs/react';
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
import { Ban, Pencil, Plus, Wallet } from 'lucide-react';
import PaymentReceiptController from '@/actions/App/Http/Controllers/PaymentReceiptController';
import {
    AsyncSearchableSelect
    
} from '@/components/async-searchable-select';
import type {SearchableSelectOption} from '@/components/async-searchable-select';
import { ConfirmActionButton } from '@/components/confirm-action-button';
import { DeleteButton } from '@/components/delete-button';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import { FormCard } from '@/components/form-card';
import {
    PaginationLinks
    
} from '@/components/pagination-links';
import type {Paginated} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { toUrl } from '@/lib/utils';
import { distributors as distributorLookups } from '@/routes/lookups';
import { createEdit, index } from '@/routes/payment-receipts';

type ReceiptRow = {
    id: number;
    number: string;
    receipt_date: string | null;
    distributor: { id: number; name: string } | null;
    payment_method_label: string;
    total_amount: string;
    status: 'draft' | 'posted' | 'cancelled';
    status_label: string;
    is_posted: boolean;
    can_edit: boolean;
    can_delete: boolean;
    can_cancel: boolean;
};

type FilterOption = {
    value: string;
    label: string;
};

type Props = {
    receipts: Paginated<ReceiptRow>;
    selected_distributor: SearchableSelectOption | null;
    filters: {
        search: string;
        status: string;
        distributor_id: number | null;
        payment_method: string;
        from_date: string | null;
        to_date: string | null;
    };
    status_options: FilterOption[];
    payment_method_options: FilterOption[];
};

const allDistributorsOption: SearchableSelectOption = {
    value: '',
    label: 'كل الموزعين',
};

export default function PaymentReceiptsIndex({
    receipts,
    selected_distributor,
    filters,
    status_options,
    payment_method_options,
}: Props) {
    const distributorId = filters.distributor_id
        ? String(filters.distributor_id)
        : '';

    const filterParams = {
        status: filters.status || undefined,
        distributor_id: distributorId || undefined,
        payment_method: filters.payment_method || undefined,
        from_date: filters.from_date || undefined,
        to_date: filters.to_date || undefined,
    };

    const applyFilters = (overrides: {
        status?: string;
        distributor_id?: string;
        payment_method?: string;
        from_date?: string;
        to_date?: string;
    }) => {
        const current =
            typeof window === 'undefined'
                ? {}
                : Object.fromEntries(
                      new URLSearchParams(window.location.search),
                  );

        const nextStatus =
            overrides.status !== undefined ? overrides.status : filters.status;
        const nextDistributorId =
            overrides.distributor_id !== undefined
                ? overrides.distributor_id
                : distributorId;
        const nextPaymentMethod =
            overrides.payment_method !== undefined
                ? overrides.payment_method
                : filters.payment_method;
        const nextFromDate =
            overrides.from_date !== undefined
                ? overrides.from_date
                : (filters.from_date ?? '');
        const nextToDate =
            overrides.to_date !== undefined
                ? overrides.to_date
                : (filters.to_date ?? '');

        router.get(
            index.url(),
            {
                ...current,
                search: filters.search || undefined,
                status: nextStatus || undefined,
                distributor_id: nextDistributorId || undefined,
                payment_method: nextPaymentMethod || undefined,
                from_date: nextFromDate || undefined,
                to_date: nextToDate || undefined,
                page: undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="سندات القبض" />
            <FormCard
                title="سندات القبض"
                description="تسجيل الدفعات وتوزيعها على فواتير المبيعات"
                icon={Wallet}
                actions={
                    <Button
                        as={Link}
                        href={toUrl(createEdit())}
                        className="inline-flex items-center gap-2"
                    >
                        <Plus className="h-4 w-4" />
                        إضافة سند قبض
                    </Button>
                }
            >
                <div className="space-y-4">
                    <div className="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-end">
                        <SearchFilter
                            url={index.url()}
                            initial={filters.search}
                            placeholder="بحث بالرقم أو اسم الموزع..."
                            className="max-w-none grow sm:max-w-md"
                            params={filterParams}
                        />
                        <div className="w-full lg:max-w-xs">
                            <Label
                                htmlFor="distributor_id"
                                className="mb-2 block"
                            >
                                الموزع
                            </Label>
                            <AsyncSearchableSelect
                                id="distributor_id"
                                name="distributor_id"
                                placeholder="كل الموزعين"
                                searchPlaceholder="ابحث عن موزع..."
                                value={distributorId}
                                initialOptions={[
                                    allDistributorsOption,
                                    ...(selected_distributor
                                        ? [selected_distributor]
                                        : []),
                                ]}
                                buildUrl={(search) =>
                                    distributorLookups.url(
                                        lookupQuery(search, {
                                            active_only: 0,
                                            include:
                                                distributorId || undefined,
                                        }),
                                    )
                                }
                                onChange={(value) =>
                                    applyFilters({ distributor_id: value })
                                }
                            />
                        </div>
                        <div className="w-full sm:w-48">
                            <Label htmlFor="status" className="mb-2 block">
                                الحالة
                            </Label>
                            <Select
                                id="status"
                                value={filters.status}
                                onChange={(event) =>
                                    applyFilters({
                                        status: event.target.value,
                                    })
                                }
                            >
                                {status_options.map((option) => (
                                    <option
                                        key={option.value || 'all'}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div className="w-full sm:w-48">
                            <Label
                                htmlFor="payment_method"
                                className="mb-2 block"
                            >
                                طريقة الدفع
                            </Label>
                            <Select
                                id="payment_method"
                                value={filters.payment_method}
                                onChange={(event) =>
                                    applyFilters({
                                        payment_method: event.target.value,
                                    })
                                }
                            >
                                {payment_method_options.map((option) => (
                                    <option
                                        key={option.value || 'all-methods'}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div className="w-full sm:w-44">
                            <Label htmlFor="from_date" className="mb-2 block">
                                من تاريخ
                            </Label>
                            <TextInput
                                id="from_date"
                                type="date"
                                value={filters.from_date ?? ''}
                                onChange={(event) =>
                                    applyFilters({
                                        from_date: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="w-full sm:w-44">
                            <Label htmlFor="to_date" className="mb-2 block">
                                إلى تاريخ
                            </Label>
                            <TextInput
                                id="to_date"
                                type="date"
                                value={filters.to_date ?? ''}
                                onChange={(event) =>
                                    applyFilters({
                                        to_date: event.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>

                    <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
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
                                    <TableHeadCell className="text-start">
                                        طريقة الدفع
                                    </TableHeadCell>
                                    <TableHeadCell className="text-end">
                                        المبلغ
                                    </TableHeadCell>
                                    <TableHeadCell className="text-center">
                                        الحالة
                                    </TableHeadCell>
                                    <TableHeadCell className="text-end">
                                        <span className="sr-only">إجراءات</span>
                                    </TableHeadCell>
                                </TableRow>
                            </TableHead>
                            <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {receipts.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={7}
                                            className="py-10 text-center text-gray-500"
                                        >
                                            لا توجد سندات قبض
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    receipts.data.map((receipt) => (
                                        <TableRow key={receipt.id}>
                                            <TableCell className="text-start font-medium">
                                                <Link
                                                    href={toUrl(
                                                        createEdit(receipt.id),
                                                    )}
                                                    className="text-primary-700 hover:underline dark:text-primary-400"
                                                    title={
                                                        receipt.can_edit
                                                            ? 'تعديل'
                                                            : 'عرض'
                                                    }
                                                >
                                                    {receipt.number}
                                                </Link>
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {receipt.receipt_date ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {receipt.distributor?.name ??
                                                    '—'}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {receipt.payment_method_label}
                                            </TableCell>
                                            <TableCell className="text-end tabular-nums">
                                                {receipt.total_amount}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <div className="flex justify-center">
                                                    <DocumentStatusBadge
                                                        status={receipt.status}
                                                        label={
                                                            receipt.status_label
                                                        }
                                                    />
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-end">
                                                <div className="inline-flex items-center justify-end gap-2">
                                                    {receipt.can_edit && (
                                                        <Button
                                                            as={Link}
                                                            href={toUrl(
                                                                createEdit(
                                                                    receipt.id,
                                                                ),
                                                            )}
                                                            size="xs"
                                                            color="light"
                                                            title="تعديل"
                                                            aria-label="تعديل"
                                                            className="inline-flex items-center justify-center p-2"
                                                        >
                                                            <Pencil className="h-3.5 w-3.5" />
                                                        </Button>
                                                    )}
                                                    {receipt.can_cancel && (
                                                        <ConfirmActionButton
                                                            href={PaymentReceiptController.cancel.url(
                                                                receipt.id,
                                                            )}
                                                            size="xs"
                                                            color="light"
                                                            title="إلغاء"
                                                            aria-label="إلغاء"
                                                            className="inline-flex items-center justify-center p-2"
                                                            confirmTitle="إلغاء سند القبض"
                                                            confirmMessage="هل أنت متأكد من إلغاء سند القبض النشط؟ سيتم عكس أثره على الذمم."
                                                            confirmLabel="إلغاء السند"
                                                            confirmingLabel="جارٍ الإلغاء..."
                                                            confirmColor="red"
                                                        >
                                                            <Ban className="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                                        </ConfirmActionButton>
                                                    )}
                                                    {receipt.can_delete && (
                                                        <DeleteButton
                                                            href={PaymentReceiptController.destroy.url(
                                                                receipt.id,
                                                            )}
                                                            confirmMessage="هل أنت متأكد من حذف سند القبض؟"
                                                        />
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    <PaginationLinks
                        meta={receipts}
                        storageKey="payment-receipts"
                    />
                </div>
            </FormCard>
        </>
    );
}

PaymentReceiptsIndex.layout = {
    breadcrumbs: [{ title: 'سندات القبض', href: index() }],
};
