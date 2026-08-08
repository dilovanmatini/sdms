import { Link, usePage } from '@inertiajs/react';
import { Button, HR } from 'flowbite-react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as generalSettingsEdit } from '@/routes/settings/general';
import { index as settingsIndex } from '@/routes/settings';
import { index as unitsIndex } from '@/routes/units';
import { index as usersIndex } from '@/routes/users';
import type { Auth, NavItem } from '@/types';

type NavItemWithAbility = NavItem & {
    ability?: string;
};

const sidebarNavItems: NavItemWithAbility[] = [
    {
        title: 'نظرة عامة',
        href: settingsIndex(),
        icon: null,
    },
    {
        title: 'عام',
        href: generalSettingsEdit(),
        icon: null,
        ability: 'manage_settings',
    },
    {
        title: 'وحدات القياس',
        href: unitsIndex(),
        icon: null,
        ability: 'manage_units',
    },
    {
        title: 'المستخدمون',
        href: usersIndex(),
        icon: null,
        ability: 'manage_users',
    },
];

export default function SystemSettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();
    const { auth } = usePage<{ auth: Auth }>().props;
    const abilities = auth.user?.abilities ?? [];

    const visibleItems = sidebarNavItems.filter(
        (item) => !item.ability || abilities.includes(item.ability),
    );

    const overviewHref = toUrl(settingsIndex());

    return (
        <div className="space-y-6">
            <Heading
                title="إعدادات النظام"
                description="إدارة إعدادات النظام والبيانات الأساسية"
            />

            <div className="flex flex-col lg:flex-row lg:gap-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1"
                        aria-label="إعدادات النظام"
                    >
                        {visibleItems.map((item, index) => {
                            const href = toUrl(item.href);
                            const active =
                                href === overviewHref
                                    ? isCurrentUrl(item.href)
                                    : isCurrentOrParentUrl(item.href);

                            return (
                                <Button
                                    key={`${href}-${index}`}
                                    as={Link}
                                    href={href}
                                    size="sm"
                                    color="light"
                                    className={cn(
                                        'w-full justify-start border-0 shadow-none',
                                        active
                                            ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                                            : 'bg-transparent text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                                    )}
                                >
                                    {item.title}
                                </Button>
                            );
                        })}
                    </nav>
                </aside>

                <HR className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-4xl">
                    <section className="space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
