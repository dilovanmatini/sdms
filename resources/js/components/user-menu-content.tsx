import { Link, router } from '@inertiajs/react';
import { LogOut, UserRound } from 'lucide-react';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { toUrl } from '@/lib/utils';
import { logout } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
    onNavigate?: () => void;
};

const itemClassName =
    'flex w-full cursor-pointer items-center justify-start gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:bg-gray-600 dark:focus:text-white';

export function UserMenuContent({ user, onNavigate }: Props) {
    const cleanup = useMobileNavigation();

    const handleNavigate = () => {
        cleanup();
        onNavigate?.();
    };

    const handleLogout = () => {
        handleNavigate();
        router.flushAll();
        router.post(toUrl(logout()));
    };

    return (
        <>
            <div className="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                <UserInfo user={user} showEmail={true} />
            </div>
            <div className="my-1 h-px bg-gray-100 dark:bg-gray-600" />
            <Link
                href={toUrl(editProfile())}
                role="menuitem"
                className={itemClassName}
                onClick={handleNavigate}
            >
                <UserRound className="size-4 shrink-0 text-gray-500 dark:text-gray-400" />
                إعدادات الحساب
            </Link>
            <div className="my-1 h-px bg-gray-100 dark:bg-gray-600" />
            <button
                type="button"
                role="menuitem"
                className={itemClassName}
                onClick={handleLogout}
                data-test="logout-button"
            >
                <LogOut className="size-4 shrink-0 text-gray-500 dark:text-gray-400" />
                تسجيل الخروج
            </button>
        </>
    );
}
