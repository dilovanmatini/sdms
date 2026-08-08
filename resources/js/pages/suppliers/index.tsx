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
import SupplierController from '@/actions/App/Http/Controllers/SupplierController';
import { ActiveBadge } from '@/components/active-badge';
import { DeleteButton } from '@/components/delete-button';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/suppliers';

type SupplierRow = {
    id: number;
    name: string;
    contact_person: string | null;
    phone: string | null;
    is_active: boolean;
    can_delete: boolean;
};

type Props = {
    suppliers: Paginated<SupplierRow>;
    filters: { search: string };
};

export default function SuppliersIndex({ suppliers, filters }: Props) {
    return (
        <>
            <Head title="الموردون" />
            <div className="space-y-6">
                <PageHeader
                    title="الموردون"
                    description="إدارة بيانات الموردين"
                    actionHref={create()}
                    actionLabel="إضافة مورد"
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
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {suppliers.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-center text-gray-500"
                                    >
                                        لا يوجد موردون
                                    </TableCell>
                                </TableRow>
                            ) : (
                                suppliers.data.map((supplier) => (
                                    <TableRow key={supplier.id}>
                                        <TableCell className="font-medium">
                                            {supplier.name}
                                        </TableCell>
                                        <TableCell>
                                            {supplier.contact_person ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {supplier.phone ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <ActiveBadge
                                                active={supplier.is_active}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(
                                                        edit(supplier.id),
                                                    )}
                                                    size="xs"
                                                    color="light"
                                                >
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Button>
                                                <DeleteButton
                                                    href={SupplierController.destroy.url(
                                                        supplier.id,
                                                    )}
                                                    disabled={
                                                        !supplier.can_delete
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

                <PaginationLinks meta={suppliers} />
            </div>
        </>
    );
}

SuppliersIndex.layout = {
    breadcrumbs: [{ title: 'الموردون', href: index() }],
};
