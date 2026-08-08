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
import { Eye, Pencil } from 'lucide-react';
import PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
import { DeleteButton } from '@/components/delete-button';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/purchases';

type PurchaseRow = {
    id: number;
    number: string;
    purchase_date: string | null;
    supplier: { id: number; name: string } | null;
    status: 'draft' | 'posted';
    status_label: string;
    is_posted: boolean;
    can_edit: boolean;
    can_delete: boolean;
};

type Props = {
    purchases: Paginated<PurchaseRow>;
    filters: { search: string };
};

export default function PurchasesIndex({ purchases, filters }: Props) {
    return (
        <>
            <Head title="المشتريات" />
            <div className="space-y-6">
                <PageHeader
                    title="المشتريات"
                    description="إدارة فواتير المشتريات وترحيلها للمخزون"
                    actionHref={create()}
                    actionLabel="إضافة مشترى"
                />

                <SearchFilter
                    url={index.url()}
                    initial={filters.search}
                    placeholder="بحث بالرقم أو اسم المورد..."
                />

                <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>الرقم</TableHeadCell>
                                <TableHeadCell>التاريخ</TableHeadCell>
                                <TableHeadCell>المورد</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {purchases.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-center text-gray-500"
                                    >
                                        لا توجد مشتريات
                                    </TableCell>
                                </TableRow>
                            ) : (
                                purchases.data.map((purchase) => (
                                    <TableRow key={purchase.id}>
                                        <TableCell className="font-medium">
                                            {purchase.number}
                                        </TableCell>
                                        <TableCell>
                                            {purchase.purchase_date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {purchase.supplier?.name ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <DocumentStatusBadge
                                                status={purchase.status}
                                                label={purchase.status_label}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(
                                                        edit(purchase.id),
                                                    )}
                                                    size="xs"
                                                    color="light"
                                                    title={
                                                        purchase.can_edit
                                                            ? 'تعديل'
                                                            : 'عرض'
                                                    }
                                                >
                                                    {purchase.can_edit ? (
                                                        <Pencil className="h-3.5 w-3.5" />
                                                    ) : (
                                                        <Eye className="h-3.5 w-3.5" />
                                                    )}
                                                </Button>
                                                <DeleteButton
                                                    href={PurchaseController.destroy.url(
                                                        purchase.id,
                                                    )}
                                                    disabled={
                                                        !purchase.can_delete
                                                    }
                                                    confirmMessage="هل أنت متأكد من حذف هذا المشترى؟"
                                                />
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <PaginationLinks meta={purchases} />
            </div>
        </>
    );
}

PurchasesIndex.layout = {
    breadcrumbs: [{ title: 'المشتريات', href: index() }],
};
