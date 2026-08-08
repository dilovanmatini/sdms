import { Link, router } from '@inertiajs/react';
import { DropdownDivider, DropdownHeader, DropdownItem } from 'flowbite-react';
import { LogOut, UserRound } from 'lucide-react';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { toUrl } from '@/lib/utils';
import { logout } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

export function UserMenuContent({ user }: Props) {
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
        router.post(toUrl(logout()));
    };

    return (
        <>
            <DropdownHeader>
                <UserInfo user={user} showEmail={true} />
            </DropdownHeader>
            <DropdownItem
                as={Link}
                href={toUrl(editProfile())}
                icon={UserRound}
                onClick={cleanup}
            >
                إعدادات الحساب
            </DropdownItem>
            <DropdownDivider />
            <DropdownItem
                icon={LogOut}
                onClick={handleLogout}
                data-test="logout-button"
            >
                تسجيل الخروج
            </DropdownItem>
        </>
    );
}
