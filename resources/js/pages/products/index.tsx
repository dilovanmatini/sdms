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
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { ActiveBadge } from '@/components/active-badge';
import { DeleteButton } from '@/components/delete-button';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/products';

type ProductRow = {
    id: number;
    code: string;
    barcode: string | null;
    name_ar: string;
    unit: { id: number; name: string; symbol: string | null } | null;
    is_active: boolean;
    category: { id: number; name: string } | null;
    can_delete: boolean;
};

type Props = {
    products: Paginated<ProductRow>;
    filters: { search: string };
};

export default function ProductsIndex({ products, filters }: Props) {
    return (
        <>
            <Head title="المنتجات" />
            <div className="space-y-6">
                <PageHeader
                    title="المنتجات"
                    description="إدارة المنتجات بدون أسعار أو كميات مخزّنة"
                    actionHref={create()}
                    actionLabel="إضافة منتج"
                />

                <SearchFilter
                    url={index.url()}
                    initial={filters.search}
                    placeholder="بحث بالرمز أو الاسم أو الباركود..."
                />

                <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>الرمز</TableHeadCell>
                                <TableHeadCell>الاسم</TableHeadCell>
                                <TableHeadCell>الصنف</TableHeadCell>
                                <TableHeadCell>الوحدة</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {products.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={6}
                                        className="text-center text-gray-500"
                                    >
                                        لا توجد منتجات
                                    </TableCell>
                                </TableRow>
                            ) : (
                                products.data.map((product) => (
                                    <TableRow key={product.id}>
                                        <TableCell className="font-medium">
                                            {product.code}
                                        </TableCell>
                                        <TableCell>{product.name_ar}</TableCell>
                                        <TableCell>
                                            {product.category?.name ?? '—'}
                                        </TableCell>
                                        <TableCell>{product.unit?.name ?? '—'}</TableCell>
                                        <TableCell>
                                            <ActiveBadge
                                                active={product.is_active}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(
                                                        edit(product.id),
                                                    )}
                                                    size="xs"
                                                    color="light"
                                                >
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Button>
                                                <DeleteButton
                                                    href={ProductController.destroy.url(
                                                        product.id,
                                                    )}
                                                    disabled={
                                                        !product.can_delete
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

                <PaginationLinks meta={products} />
            </div>
        </>
    );
}

ProductsIndex.layout = {
    breadcrumbs: [{ title: 'المنتجات', href: index() }],
};
