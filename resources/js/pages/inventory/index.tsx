import { Head, router } from '@inertiajs/react';
import {
    Label,
    Select,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
} from 'flowbite-react';
import { Warehouse } from 'lucide-react';
import {
    AsyncSearchableSelect,
    type SearchableSelectOption,
} from '@/components/async-searchable-select';
import { FormCard } from '@/components/form-card';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { index } from '@/routes/inventory';
import { categories as categoryLookups } from '@/routes/lookups';

type InventoryRow = {
    id: number;
    code: string;
    name_ar: string;
    unit: { id: number; name: string; symbol: string | null } | null;
    category: { id: number; name: string } | null;
    available_quantity: string;
};

type StockOption = {
    value: string;
    label: string;
};

type Props = {
    products: Paginated<InventoryRow>;
    selected_category: SearchableSelectOption | null;
    filters: {
        search: string;
        category_id: number | null;
        stock: string;
    };
    stock_options: StockOption[];
};

const allCategoriesOption: SearchableSelectOption = {
    value: '',
    label: 'كل الأصناف',
};

export default function InventoryIndex({
    products,
    selected_category,
    filters,
    stock_options,
}: Props) {
    const categoryId = filters.category_id
        ? String(filters.category_id)
        : '';

    const applyFilters = (overrides: {
        category_id?: string;
        stock?: string;
    }) => {
        const nextCategoryId =
            overrides.category_id !== undefined
                ? overrides.category_id
                : categoryId;
        const nextStock =
            overrides.stock !== undefined ? overrides.stock : filters.stock;

        const current =
            typeof window === 'undefined'
                ? {}
                : Object.fromEntries(
                      new URLSearchParams(window.location.search),
                  );

        router.get(
            index.url(),
            {
                ...current,
                search: filters.search || undefined,
                category_id: nextCategoryId || undefined,
                stock: nextStock || undefined,
                page: undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="المخزون" />
            <FormCard
                title="المخزون"
                description="الكميات المتاحة محسوبة من حركات المخزون"
                icon={Warehouse}
            >
                <div className="space-y-4">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-end">
                        <SearchFilter
                            url={index.url()}
                            initial={filters.search}
                            placeholder="بحث بالرمز أو الاسم أو الباركود..."
                            className="max-w-none grow sm:max-w-md"
                            params={{
                                category_id: categoryId || undefined,
                                stock: filters.stock || undefined,
                            }}
                        />
                        <div className="w-full lg:max-w-xs">
                            <Label htmlFor="category_id" className="mb-2 block">
                                الصنف
                            </Label>
                            <AsyncSearchableSelect
                                id="category_id"
                                name="category_id"
                                placeholder="كل الأصناف"
                                searchPlaceholder="ابحث عن صنف..."
                                value={categoryId}
                                initialOptions={[
                                    allCategoriesOption,
                                    ...(selected_category
                                        ? [selected_category]
                                        : []),
                                ]}
                                buildUrl={(search) =>
                                    categoryLookups.url(
                                        lookupQuery(search, {
                                            include:
                                                categoryId || undefined,
                                        }),
                                    )
                                }
                                onChange={(value) =>
                                    applyFilters({ category_id: value })
                                }
                            />
                        </div>
                        <div className="w-full sm:w-48">
                            <Label htmlFor="stock" className="mb-2 block">
                                الكمية
                            </Label>
                            <Select
                                id="stock"
                                value={filters.stock}
                                onChange={(event) =>
                                    applyFilters({
                                        stock: event.target.value,
                                    })
                                }
                            >
                                {stock_options.map((option) => (
                                    <option
                                        key={option.value || 'all'}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </Select>
                        </div>
                    </div>

                    <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableHeadCell className="text-start">
                                        الرمز
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        المنتج
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الصنف
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الوحدة
                                    </TableHeadCell>
                                    <TableHeadCell className="text-end">
                                        الكمية المتاحة
                                    </TableHeadCell>
                                </TableRow>
                            </TableHead>
                            <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {products.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
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
                                                {product.category?.name ??
                                                    '—'}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {product.unit
                                                    ? product.unit.symbol
                                                        ? `${product.unit.name} (${product.unit.symbol})`
                                                        : product.unit.name
                                                    : '—'}
                                            </TableCell>
                                            <TableCell className="text-end font-medium tabular-nums">
                                                {product.available_quantity}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    <PaginationLinks meta={products} storageKey="inventory" />
                </div>
            </FormCard>
        </>
    );
}

InventoryIndex.layout = {
    breadcrumbs: [{ title: 'المخزون', href: index() }],
};
