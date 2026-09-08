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
import { Package, Pencil, Plus } from 'lucide-react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { ActiveBadge } from '@/components/active-badge';
import { ActiveStatusFilter } from '@/components/active-status-filter';
import type { ActiveStatusOption } from '@/components/active-status-filter';
import { DeleteButton } from '@/components/delete-button';
import { FormCard } from '@/components/form-card';
import {
    PaginationLinks
    
} from '@/components/pagination-links';
import type {Paginated} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { createEdit, index } from '@/routes/products';

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
    filters: { search: string; is_active: string };
    active_status_options: ActiveStatusOption[];
};

export default function ProductsIndex({
    products,
    filters,
    active_status_options,
}: Props) {
    return (
        <>
            <Head title="المنتجات" />
            <FormCard
                title="المنتجات"
                description="إدارة المنتجات بدون أسعار أو كميات مخزّنة"
                icon={Package}
                actions={
                    <Button
                        as={Link}
                        href={toUrl(createEdit())}
                        className="inline-flex items-center gap-2"
                    >
                        <Plus className="h-4 w-4" />
                        إضافة منتج
                    </Button>
                }
            >
                <div className="space-y-4">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <SearchFilter
                            url={index.url()}
                            initial={filters.search}
                            placeholder="بحث بالرمز أو الاسم أو الباركود..."
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
                                        الرمز
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الاسم
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الصنف
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الوحدة
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
                                {products.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-10 text-center text-gray-500"
                                        >
                                            لا توجد منتجات
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    products.data.map((product) => (
                                        <TableRow key={product.id}>
                                            <TableCell className="text-start font-medium">
                                                {product.code}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {product.name_ar}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {product.category?.name ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {product.unit?.name ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <div className="flex justify-center">
                                                    <ActiveBadge
                                                        active={
                                                            product.is_active
                                                        }
                                                    />
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-end">
                                                <div className="inline-flex items-center justify-end gap-2">
                                                    <Button
                                                        as={Link}
                                                        href={toUrl(
                                                            createEdit(
                                                                product.id,
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

                    <PaginationLinks meta={products} storageKey="products" />
                </div>
            </FormCard>
        </>
    );
}

ProductsIndex.layout = {
    breadcrumbs: [{ title: 'المنتجات', href: index() }],
};
