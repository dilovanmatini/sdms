import { Head, Link, usePage } from '@inertiajs/react';
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
import UserController from '@/actions/App/Http/Controllers/Settings/UserController';
import { ActiveBadge } from '@/components/active-badge';
import { DeleteButton } from '@/components/delete-button';
import { PageHeader } from '@/components/page-header';
import {
    PaginationLinks,
    type Paginated,
} from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { create, edit, index } from '@/routes/users';
import type { Auth } from '@/types';

type UserRow = {
    id: number;
    name: string;
    username: string;
    email: string | null;
    role: string;
    role_label: string;
    is_active: boolean;
};

type Props = {
    users: Paginated<UserRow>;
    filters: { search: string };
};

export default function UsersIndex({ users, filters }: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <>
            <Head title="المستخدمون" />
            <div className="space-y-6">
                <PageHeader
                    title="المستخدمون"
                    description="إدارة حسابات النظام والأدوار"
                    actionHref={create()}
                    actionLabel="إضافة مستخدم"
                />

                <SearchFilter
                    url={index.url()}
                    initial={filters.search}
                    placeholder="بحث بالاسم أو اسم المستخدم..."
                />

                <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>الاسم</TableHeadCell>
                                <TableHeadCell>اسم المستخدم</TableHeadCell>
                                <TableHeadCell>الدور</TableHeadCell>
                                <TableHeadCell>الحالة</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">إجراءات</span>
                                </TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {users.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-center text-gray-500"
                                    >
                                        لا يوجد مستخدمون
                                    </TableCell>
                                </TableRow>
                            ) : (
                                users.data.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell className="font-medium">
                                            {user.name}
                                        </TableCell>
                                        <TableCell>{user.username}</TableCell>
                                        <TableCell>
                                            {user.role_label}
                                        </TableCell>
                                        <TableCell>
                                            <ActiveBadge
                                                active={user.is_active}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    as={Link}
                                                    href={toUrl(edit(user.id))}
                                                    size="xs"
                                                    color="light"
                                                >
                                                    <Pencil className="h-3.5 w-3.5" />
                                                </Button>
                                                <DeleteButton
                                                    href={UserController.destroy.url(
                                                        user.id,
                                                    )}
                                                    disabled={
                                                        auth.user.id === user.id
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

                <PaginationLinks meta={users} />
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [{ title: 'المستخدمون', href: index() }],
};
