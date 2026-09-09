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
import { Pencil, Plus, Ruler } from 'lucide-react';
import UnitController from '@/actions/App/Http/Controllers/Settings/UnitController';
import { ActiveBadge } from '@/components/active-badge';
import { ActiveStatusFilter } from '@/components/active-status-filter';
import type { ActiveStatusOption } from '@/components/active-status-filter';
import { DeleteButton } from '@/components/delete-button';
import { FormCard } from '@/components/form-card';
import { PaginationLinks } from '@/components/pagination-links';
import type { Paginated } from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { createEdit, index } from '@/routes/units';

type UnitRow = {
    id: number;
    name: string;
    symbol: string | null;
    is_active: boolean;
    products_count: number;
    can_delete: boolean;
};

type Props = {
    units: Paginated<UnitRow>;
    filters: { search: string; is_active: string };
    active_status_options: ActiveStatusOption[];
};

export default function UnitsIndex({
    units,
    filters,
    active_status_options,
}: Props) {
    return (
        <>
            <Head title="وحدات القياس" />
            <FormCard
                title="وحدات القياس"
                description="إدارة وحدات القياس المستخدمة في المنتجات"
                icon={Ruler}
                actions={
                    <Button
                        as={Link}
                        href={toUrl(createEdit())}
                        className="inline-flex items-center gap-2"
                    >
                        <Plus className="h-4 w-4" />
                        إضافة وحدة
                    </Button>
                }
            >
                <div className="space-y-4">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <SearchFilter
                            url={index.url()}
                            initial={filters.search}
                            placeholder="بحث بالاسم أو الرمز..."
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
                                        الرمز
                                    </TableHeadCell>
                                    <TableHeadCell className="text-center">
                                        المنتجات
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
                                {units.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-gray-500"
                                        >
                                            لا توجد وحدات قياس
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    units.data.map((unit) => (
                                        <TableRow key={unit.id}>
                                            <TableCell className="text-start font-medium">
                                                {unit.name}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {unit.symbol ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-center tabular-nums">
                                                {unit.products_count}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <div className="flex justify-center">
                                                    <ActiveBadge
                                                        active={unit.is_active}
                                                    />
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-end">
                                                <div className="inline-flex items-center justify-end gap-2">
                                                    <Button
                                                        as={Link}
                                                        href={toUrl(
                                                            createEdit(unit.id),
                                                        )}
                                                        size="xs"
                                                        color="light"
                                                        title="تعديل"
                                                        aria-label="تعديل"
                                                        className="inline-flex items-center justify-center p-2"
                                                    >
                                                        <Pencil className="h-3.5 w-3.5" />
                                                    </Button>
                                                    <DeleteButton
                                                        href={UnitController.destroy.url(
                                                            unit.id,
                                                        )}
                                                        disabled={
                                                            !unit.can_delete
                                                        }
                                                    />
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    <PaginationLinks meta={units} storageKey="units" />
                </div>
            </FormCard>
        </>
    );
}

UnitsIndex.layout = {
    breadcrumbs: [{ title: 'وحدات القياس', href: index() }],
};
