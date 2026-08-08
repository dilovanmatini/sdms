import { usePage } from '@inertiajs/react';
import { Dropdown } from 'flowbite-react';
import { ChevronsUpDown } from 'lucide-react';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { cn } from '@/lib/utils';

export function NavUser({ collapsed = false }: { collapsed?: boolean }) {
    const { auth } = usePage().props;

    if (!auth.user) {
        return null;
    }

    return (
        <Dropdown
            inline
            arrowIcon={false}
            placement={collapsed ? 'left' : 'top'}
            className="min-w-56"
            label={
                <div
                    className={cn(
                        'flex w-full items-center rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700',
                        collapsed ? 'justify-center' : 'gap-2',
                    )}
                    data-test="sidebar-menu-button"
                >
                    <UserInfo
                        user={auth.user}
                        showEmail={false}
                        showName={!collapsed}
                        className={collapsed ? undefined : 'min-w-0 flex-1'}
                    />
                    {!collapsed && (
                        <ChevronsUpDown className="ms-auto size-4 shrink-0 text-gray-500" />
                    )}
                </div>
            }
        >
            <UserMenuContent user={auth.user} />
        </Dropdown>
    );
}
