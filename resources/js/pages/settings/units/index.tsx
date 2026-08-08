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
import { Pencil } from 'lucide-react';
import UnitController from '@/actions/App/Http/Controllers/Settings/UnitController';
import { ActiveBadge } from '@/components/active-badge';
import { DeleteButton } from '@/components/delete-button';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/units';

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
    filters: { search: string };
};

export default function UnitsIndex({ units, filters }: Props) {
    return (
        <>
            <Head title="وحدات القياس" />
            <div className="space-y-6">
                <PageHeader
                    title="وحدات القياس"
                    description="إدارة وحدات القياس المستخدمة في المنتجات"
                    actionHref={create()}
                    actionLabel="إضافة وحدة"
                />

                <SearchFilter
                    url={index.url()}
                    initial={filters.search}
                    placeholder="بحث بالاسم أو الرمز..."
                />

                <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>الاسم</TableHeadCell>
                                <TableHeadCell>الرمز</TableHeadCell>
                                <TableHeadCell>المنتجات</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {units.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-center text-gray-500"
                                    >
                                        لا توجد وحدات قياس
                                    </TableCell>
                                </TableRow>
                            ) : (
                                units.data.map((unit) => (
                                    <TableRow key={unit.id}>
                                        <TableCell className="font-medium">
                                            {unit.name}
                                        </TableCell>
                                        <TableCell>
                                            {unit.symbol ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {unit.products_count}
                                        </TableCell>
                                        <TableCell>
                                            <ActiveBadge
                                                active={unit.is_active}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(edit(unit.id))}
                                                    size="xs"
                                                    color="light"
                                                >
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Button>
                                                <DeleteButton
                                                    href={UnitController.destroy.url(
                                                        unit.id,
                                                    )}
                                                    disabled={!unit.can_delete}
                                                />
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <PaginationLinks meta={units} />
            </div>
        </>
    );
}

UnitsIndex.layout = {
    breadcrumbs: [{ title: 'وحدات القياس', href: index() }],
};
