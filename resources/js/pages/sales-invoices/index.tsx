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
import SalesInvoiceController from '@/actions/App/Http/Controllers/SalesInvoiceController';
import { DeleteButton } from '@/components/delete-button';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/sales-invoices';

type InvoiceRow = {
    id: number;
    number: string;
    invoice_date: string | null;
    distributor: { id: number; name: string } | null;
    grand_total: string;
    status: 'draft' | 'posted';
    status_label: string;
    is_posted: boolean;
    can_edit: boolean;
    can_delete: boolean;
};

type Props = {
    invoices: Paginated<InvoiceRow>;
    filters: { search: string };
};

export default function SalesInvoicesIndex({ invoices, filters }: Props) {
    return (
        <>
            <Head title="فواتير المبيعات" />
            <div className="space-y-6">
                <PageHeader
                    title="فواتير المبيعات"
                    description="إدارة فواتير المبيعات وترحيلها للمخزون والذمم"
                    actionHref={create()}
                    actionLabel="إضافة فاتورة"
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
                                <TableHeadCell>الإجمالي</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {invoices.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={6}
                                        className="text-center text-gray-500"
                                    >
                                        لا توجد فواتير
                                    </TableCell>
                                </TableRow>
                            ) : (
                                invoices.data.map((invoice) => (
                                    <TableRow key={invoice.id}>
                                        <TableCell className="font-medium">
                                            {invoice.number}
                                        </TableCell>
                                        <TableCell>
                                            {invoice.invoice_date ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {invoice.distributor?.name ?? '—'}
                                        </TableCell>
                                        <TableCell className="tabular-nums">
                                            {invoice.grand_total}
                                        </TableCell>
                                        <TableCell>
                                            <DocumentStatusBadge
                                                status={invoice.status}
                                                label={invoice.status_label}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(
                                                        edit(invoice.id),
                                                    )}
                                                    size="xs"
                                                    color="light"
                                                    title={
                                                        invoice.can_edit
                                                            ? 'تعديل'
                                                            : 'عرض'
                                                    }
                                                >
                                                    {invoice.can_edit ? (
                                                        <Pencil className="h-3.5 w-3.5" />
                                                    ) : (
                                                        <Eye className="h-3.5 w-3.5" />
                                                    )}
                                                </Button>
                                                <DeleteButton
                                                    href={SalesInvoiceController.destroy.url(
                                                        invoice.id,
                                                    )}
                                                    disabled={
                                                        !invoice.can_delete
                                                    }
                                                    confirmMessage="هل أنت متأكد من حذف هذه الفاتورة؟"
                                                />
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                <PaginationLinks meta={invoices} />
            </div>
        </>
    );
}

SalesInvoicesIndex.layout = {
    breadcrumbs: [{ title: 'فواتير المبيعات', href: index() }],
};
