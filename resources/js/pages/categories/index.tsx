import { Form, Head, Link } from '@inertiajs/react';
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
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import { ActiveBadge } from '@/components/active-badge';
import { DeleteButton } from '@/components/delete-button';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/categories';

type CategoryRow = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    products_count: number;
    can_delete: boolean;
};

type Props = {
    categories: Paginated<CategoryRow>;
    filters: { search: string };
};

export default function CategoriesIndex({ categories, filters }: Props) {
    return (
        <>
            <Head title="الأصناف" />
            <div className="space-y-6">
                <PageHeader
                    title="الأصناف"
                    description="إدارة أصناف المنتجات"
                    actionHref={create()}
                    actionLabel="إضافة صنف"
                />

                <SearchFilter
                    url={index.url()}
                    initial={filters.search}
                    placeholder="بحث بالاسم أو الوصف..."
                />

                <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>الاسم</TableHeadCell>
                                <TableHeadCell>الوصف</TableHeadCell>
                                <TableHeadCell>المنتجات</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {categories.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-center text-gray-500"
                                    >
                                        لا توجد أصناف
                                    </TableCell>
                                </TableRow>
                            ) : (
                                categories.data.map((category) => (
                                    <TableRow key={category.id}>
                                        <TableCell className="font-medium">
                                            {category.name}
                                        </TableCell>
                                        <TableCell>
                                            {category.description ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {category.products_count}
                                        </TableCell>
                                        <TableCell>
                                            <ActiveBadge
                                                active={category.is_active}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(
                                                        edit(category.id),
                                                    )}
                                                    size="xs"
                                                    color="light"
                                                >
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Button>
                                                <DeleteButton
                                                    href={CategoryController.destroy.url(
                                                        category.id,
                                                    )}
                                                    disabled={
                                                        !category.can_delete
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

                <PaginationLinks meta={categories} />
            </div>
        </>
    );
}

CategoriesIndex.layout = {
    breadcrumbs: [{ title: 'الأصناف', href: index() }],
};
