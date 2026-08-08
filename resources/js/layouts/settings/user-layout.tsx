import { Link } from '@inertiajs/react';
import { Button, HR } from 'flowbite-react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'الملف الشخصي',
        href: edit(),
        icon: null,
    },
    {
        title: 'الأمان',
        href: editSecurity(),
        icon: null,
    },
    {
        title: 'المظهر',
        href: editAppearance(),
        icon: null,
    },
];

export default function UserSettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <div className="space-y-6">
            <Heading
                title="إعدادات الحساب"
                description="إدارة الملف الشخصي والأمان والمظهر"
            />

            <div className="flex flex-col lg:flex-row lg:gap-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1"
                        aria-label="إعدادات الحساب"
                    >
                        {sidebarNavItems.map((item, index) => {
                            const href = toUrl(item.href);
                            const active = isCurrentUrl(item.href);

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
