import { Head, Link } from '@inertiajs/react';
import {
    Button,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
} from 'flowbite-react';
import { Plus, Users } from 'lucide-react';
import DistributorController from '@/actions/App/Http/Controllers/DistributorController';
import { ActiveBadge } from '@/components/active-badge';
import { ActiveStatusFilter } from '@/components/active-status-filter';
import type { ActiveStatusOption } from '@/components/active-status-filter';
import { DistributorActionsMenu } from '@/components/distributor-actions-menu';
import { FormCard } from '@/components/form-card';
import { PaginationLinks } from '@/components/pagination-links';
import type { Paginated } from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { createEdit, index } from '@/routes/distributors';

type DistributorRow = {
    id: number;
    name: string;
    contact_person: string | null;
    phone: string | null;
    credit_limit: number | null;
    is_active: boolean;
    can_delete: boolean;
};

type Props = {
    distributors: Paginated<DistributorRow>;
    filters: { search: string; is_active: string };
    active_status_options: ActiveStatusOption[];
};

export default function DistributorsIndex({
    distributors,
    filters,
    active_status_options,
}: Props) {
    return (
        <>
            <Head title="الموزعون" />
            <FormCard
                title="الموزعون"
                description="إدارة بيانات الموزعين"
                icon={Users}
                actions={
                    <Button
                        as={Link}
                        href={toUrl(createEdit())}
                        className="inline-flex items-center gap-2"
                    >
                        <Plus className="h-4 w-4" />
                        إضافة موزع
                    </Button>
                }
            >
                <div className="space-y-4">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <SearchFilter
                            url={index.url()}
                            initial={filters.search}
                            placeholder="بحث بالاسم أو جهة الاتصال أو الهاتف..."
                            className="max-w-none grow sm:max-w-md"
                            params={{
                                is_active: filters.is_active || undefined,
                            }}
                        />
                        <ActiveStatusFilter
                            url={index.url()}
                            value={filters.is_active}
                            search={filters.search}
                            options={active_status_options}
                        />
                    </div>

                    <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableHeadCell className="text-start">
                                        الاسم
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        جهة الاتصال
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الهاتف
                                    </TableHeadCell>
                                    <TableHeadCell className="text-center">
                                        حد الائتمان
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
                                {distributors.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-10 text-center text-gray-500"
                                        >
                                            لا يوجد موزعون
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    distributors.data.map((distributor) => (
                                        <TableRow key={distributor.id}>
                                            <TableCell className="text-start font-medium">
                                                {distributor.name}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {distributor.contact_person ??
                                                    '—'}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {distributor.phone ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-center tabular-nums">
                                                {distributor.credit_limit ??
                                                    '—'}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <div className="flex justify-center">
                                                    <ActiveBadge
                                                        active={
                                                            distributor.is_active
                                                        }
                                                    />
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-end">
                                                <DistributorActionsMenu
                                                    distributorId={
                                                        distributor.id
                                                    }
                                                    distributorLabel={
                                                        distributor.name
                                                    }
                                                    buttonSize="xs"
                                                    deleteHref={DistributorController.destroy.url(
                                                        distributor.id,
                                                    )}
                                                    canDelete={
                                                        distributor.can_delete
                                                    }
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    <PaginationLinks
                        meta={distributors}
                        storageKey="distributors"
                    />
                </div>
            </FormCard>
        </>
    );
}

DistributorsIndex.layout = {
    breadcrumbs: [{ title: 'الموزعون', href: index() }],
};
