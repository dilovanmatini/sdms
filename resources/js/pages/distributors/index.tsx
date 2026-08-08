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
import DistributorController from '@/actions/App/Http/Controllers/DistributorController';
import { ActiveBadge } from '@/components/active-badge';
import { DeleteButton } from '@/components/delete-button';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/distributors';

type DistributorRow = {
    id: number;
    name: string;
    contact_person: string | null;
    phone: string | null;
    credit_limit: string | null;
    is_active: boolean;
    can_delete: boolean;
};

type Props = {
    distributors: Paginated<DistributorRow>;
    filters: { search: string };
};

export default function DistributorsIndex({ distributors, filters }: Props) {
    return (
        <>
            <Head title="الموزعون" />
            <div className="space-y-6">
                <PageHeader
                    title="الموزعون"
                    description="إدارة بيانات الموزعين"
                    actionHref={create()}
                    actionLabel="إضافة موزع"
                />

                <SearchFilter
                    url={index.url()}
                    initial={filters.search}
                    placeholder="بحث بالاسم أو جهة الاتصال أو الهاتف..."
                />

                <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>الاسم</TableHeadCell>
                                <TableHeadCell>جهة الاتصال</TableHeadCell>
                                <TableHeadCell>الهاتف</TableHeadCell>
                                <TableHeadCell>حد الائتمان</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {distributors.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={6}
                                        className="text-center text-gray-500"
                                    >
                                        لا يوجد موزعون
                                    </TableCell>
                                </TableRow>
                            ) : (
                                distributors.data.map((distributor) => (
                                    <TableRow key={distributor.id}>
                                        <TableCell className="font-medium">
                                            {distributor.name}
                                        </TableCell>
                                        <TableCell>
                                            {distributor.contact_person ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {distributor.phone ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {distributor.credit_limit ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <ActiveBadge
                                                active={distributor.is_active}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(
                                                        edit(distributor.id),
                                                    )}
                                                    size="xs"
                                                    color="light"
                                                >
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Button>
                                                <DeleteButton
                                                    href={DistributorController.destroy.url(
                                                        distributor.id,
                                                    )}
                                                    disabled={
                                                        !distributor.can_delete
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

                <PaginationLinks meta={distributors} />
            </div>
        </>
    );
}

DistributorsIndex.layout = {
    breadcrumbs: [{ title: 'الموزعون', href: index() }],
};
