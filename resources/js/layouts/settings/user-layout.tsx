import { Palette, Shield, UserRound } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { SettingsSubNav } from '@/components/settings-sub-nav';
import type { SettingsNavItem } from '@/components/settings-sub-nav';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

const sidebarNavItems: SettingsNavItem[] = [
    {
        title: 'الملف الشخصي',
        href: edit(),
        icon: UserRound,
    },
    {
        title: 'الأمان',
        href: editSecurity(),
        icon: Shield,
    },
    {
        title: 'المظهر',
        href: editAppearance(),
        icon: Palette,
    },
];

export default function UserSettingsLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex flex-col gap-6">
            <SettingsSubNav
                items={sidebarNavItems}
                ariaLabel="إعدادات الحساب"
            />

            <div className="min-w-0">{children}</div>
        </div>
    );
}
