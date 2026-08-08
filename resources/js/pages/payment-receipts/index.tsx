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
import PaymentReceiptController from '@/actions/App/Http/Controllers/PaymentReceiptController';
import { DeleteButton } from '@/components/delete-button';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/payment-receipts';

type ReceiptRow = {
    id: number;
    number: string;
    receipt_date: string | null;
    distributor: { id: number; name: string } | null;
    payment_method_label: string;
    total_amount: string;
    status: 'draft' | 'posted';
    status_label: string;
    is_posted: boolean;
    can_edit: boolean;
    can_delete: boolean;
};

type Props = {
    receipts: Paginated<ReceiptRow>;
    filters: { search: string };
};

export default function PaymentReceiptsIndex({ receipts, filters }: Props) {
    return (
        <>
            <Head title="سندات القبض" />
            <div className="space-y-6">
                <PageHeader
                    title="سندات القبض"
                    description="تسجيل الدفعات وتوزيعها على فواتير المبيعات"
                    actionHref={create()}
                    actionLabel="إضافة سند قبض"
                />

                <SearchFilter
                    url={index.url()}
                    initial={filters.search}
                    placeholder="بحث بالرقم أو اسم الموزع..."
                />

                <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>الرقم</TableHeadCell>
                                <TableHeadCell>التاريخ</TableHeadCell>
                                <TableHeadCell>الموزع</TableHeadCell>
                                <TableHeadCell>طريقة الدفع</TableHeadCell>
                                <TableHeadCell>المبلغ</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {receipts.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={7}
                                        className="text-center text-gray-500"
                                    >
                                        لا توجد سندات قبض
                                    </TableCell>
                                </TableRow>
                            ) : (
                                receipts.data.map((receipt) => (
                                    <TableRow key={receipt.id}>
                                        <TableCell className="font-medium">
                                            {receipt.number}
                                        </TableCell>
                                        <TableCell>
                                            {receipt.receipt_date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {receipt.distributor?.name ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {receipt.payment_method_label}
                                        </TableCell>
                                        <TableCell className="tabular-nums">
                                            {receipt.total_amount}
                                        </TableCell>
                                        <TableCell>
                                            <DocumentStatusBadge
                                                status={receipt.status}
                                                label={receipt.status_label}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(
                                                        edit(receipt.id),
                                                    )}
                                                    size="xs"
                                                    color="light"
                                                    title={
                                                        receipt.can_edit
                                                            ? 'تعديل'
                                                            : 'عرض'
                                                    }
                                                >
                                                    {receipt.can_edit ? (
                                                        <Pencil className="h-3.5 w-3.5" />
                                                    ) : (
                                                        <Eye className="h-3.5 w-3.5" />
                                                    )}
                                                </Button>
                                                <DeleteButton
                                                    href={PaymentReceiptController.destroy.url(
                                                        receipt.id,
                                                    )}
                                                    disabled={
                                                        !receipt.can_delete
                                                    }
                                                    confirmMessage="هل أنت متأكد من حذف سند القبض؟"
                                                />
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <PaginationLinks meta={receipts} />
            </div>
        </>
    );
}

PaymentReceiptsIndex.layout = {
    breadcrumbs: [{ title: 'سندات القبض', href: index() }],
};
