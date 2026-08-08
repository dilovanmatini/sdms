import { Head } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
} from 'flowbite-react';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { index } from '@/routes/inventory';

type InventoryRow = {
    id: number;
    code: string;
    name_ar: string;
    unit: { id: number; name: string; symbol: string | null } | null;
    category: { id: number; name: string } | null;
    available_quantity: string;
};

type Props = {
    products: Paginated<InventoryRow>;
    filters: { search: string };
};

export default function InventoryIndex({ products, filters }: Props) {
    return (
        <>
            <Head title="المخزون" />
            <div className="space-y-6">
                <PageHeader
                    title="المخزون"
                    description="الكميات المتاحة محسوبة من حركات المخزون"
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
                                <TableHeadCell>المنتج</TableHeadCell>
                                <TableHeadCell>الصنف</TableHeadCell>
                                <TableHeadCell>الوحدة</TableHeadCell>
                                <TableHeadCell>الكمية المتاحة</TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {products.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
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
                                        <TableCell>
                                            {product.unit
                                                ? product.unit.symbol
                                                    ? `${product.unit.name} (${product.unit.symbol})`
                                                    : product.unit.name
                                                : '—'}
                                        </TableCell>
                                        <TableCell className="font-medium tabular-nums">
                                            {product.available_quantity}
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

InventoryIndex.layout = {
    breadcrumbs: [{ title: 'المخزون', href: index() }],
};
