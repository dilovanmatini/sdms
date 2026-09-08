import { usePage } from '@inertiajs/react';
import { LayoutGrid, Ruler, Settings2, UserCog } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import {
    SettingsSubNav,
} from '@/components/settings-sub-nav';
import type { SettingsNavItem } from '@/components/settings-sub-nav';
import { toUrl } from '@/lib/utils';
import { index as settingsIndex } from '@/routes/settings';
import { edit as generalSettingsEdit } from '@/routes/settings/general';
import { index as unitsIndex } from '@/routes/units';
import { index as usersIndex } from '@/routes/users';
import type { Auth } from '@/types';

const sidebarNavItems: SettingsNavItem[] = [
    {
        title: 'نظرة عامة',
        href: settingsIndex(),
        icon: LayoutGrid,
    },
    {
        title: 'عام',
        href: generalSettingsEdit(),
        icon: Settings2,
        ability: 'manage_settings',
    },
    {
        title: 'وحدات القياس',
        href: unitsIndex(),
        icon: Ruler,
        ability: 'manage_units',
    },
    {
        title: 'المستخدمون',
        href: usersIndex(),
        icon: UserCog,
        ability: 'manage_users',
    },
];

export default function SystemSettingsLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const abilities = auth.user?.abilities ?? [];

    const visibleItems = sidebarNavItems.filter(
        (item) => !item.ability || abilities.includes(item.ability),
    );

    return (
        <div className="flex flex-col gap-6">
            <SettingsSubNav
                items={visibleItems}
                ariaLabel="إعدادات النظام"
                exactHref={toUrl(settingsIndex())}
            />

            <div className="min-w-0">{children}</div>
        </div>
    );
}
