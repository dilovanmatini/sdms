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
import { Pencil, Plus, UserCog } from 'lucide-react';
import UserController from '@/actions/App/Http/Controllers/Settings/UserController';
import { ActiveBadge } from '@/components/active-badge';
import { ActiveStatusFilter } from '@/components/active-status-filter';
import type { ActiveStatusOption } from '@/components/active-status-filter';
import { DeleteButton } from '@/components/delete-button';
import { FormCard } from '@/components/form-card';
import { PaginationLinks } from '@/components/pagination-links';
import type { Paginated } from '@/components/pagination-links';
import { SearchFilter } from '@/components/search-filter';
import { toUrl } from '@/lib/utils';
import { createEdit, index } from '@/routes/users';
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
    filters: { search: string; is_active: string };
    active_status_options: ActiveStatusOption[];
};

export default function UsersIndex({
    users,
    filters,
    active_status_options,
}: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <>
            <Head title="المستخدمون" />
            <FormCard
                title="المستخدمون"
                description="إدارة حسابات النظام والأدوار"
                icon={UserCog}
                actions={
                    <Button
                        as={Link}
                        href={toUrl(createEdit())}
                        className="inline-flex items-center gap-2"
                    >
                        <Plus className="h-4 w-4" />
                        إضافة مستخدم
                    </Button>
                }
            >
                <div className="space-y-4">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <SearchFilter
                            url={index.url()}
                            initial={filters.search}
                            placeholder="بحث بالاسم أو اسم المستخدم..."
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
                                        الاسم
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        اسم المستخدم
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الدور
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
                                {users.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-gray-500"
                                        >
                                            لا يوجد مستخدمون
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    users.data.map((user) => (
                                        <TableRow key={user.id}>
                                            <TableCell className="text-start font-medium">
                                                {user.name}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {user.username}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {user.role_label}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <div className="flex justify-center">
                                                    <ActiveBadge
                                                        active={user.is_active}
                                                    />
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-end">
                                                <div className="inline-flex items-center justify-end gap-2">
                                                    <Button
                                                        as={Link}
                                                        href={toUrl(
                                                            createEdit(user.id),
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
                                                        href={UserController.destroy.url(
                                                            user.id,
                                                        )}
                                                        disabled={
                                                            auth.user.id ===
                                                            user.id
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

                    <PaginationLinks meta={users} storageKey="users" />
                </div>
            </FormCard>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [{ title: 'المستخدمون', href: index() }],
};
