import { Link, usePage } from '@inertiajs/react';
import {
    Avatar,
    Button,
    Drawer,
    DrawerHeader,
    DrawerItems,
    Dropdown,
    Navbar,
    NavbarBrand,
} from 'flowbite-react';
import { LayoutGrid, Menu, X } from 'lucide-react';
import { useState } from 'react';
import AppLogo from '@/components/app-logo';
import AppLogoIcon from '@/components/app-logo-icon';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { UserMenuContent } from '@/components/user-menu-content';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useInitials } from '@/hooks/use-initials';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const mainNavItems: NavItem[] = [
    {
        title: 'الصفحة الرئيسية',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [];

export function AppHeader({ breadcrumbs = [] }: Props) {
    const page = usePage();
    const { auth } = page.props;
    const getInitials = useInitials();
    const { isCurrentUrl } = useCurrentUrl();
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <>
            <Navbar
                fluid
                className="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <div className="flex items-center gap-2">
                    <Button
                        color="light"
                        size="sm"
                        className="lg:hidden"
                        onClick={() => setMobileOpen(true)}
                        aria-label="فتح القائمة"
                    >
                        <Menu className="h-5 w-5" />
                    </Button>
                    <NavbarBrand as={Link} href={toUrl(dashboard())}>
                        <AppLogo />
                    </NavbarBrand>
                </div>

                <div className="hidden items-center gap-4 lg:flex">
                    {mainNavItems.map((item) => (
                        <Link
                            key={item.title}
                            href={item.href}
                            className={`inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm ${
                                isCurrentUrl(item.href)
                                    ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-700 dark:text-white'
                                    : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'
                            }`}
                        >
                            {item.icon && <item.icon className="h-4 w-4" />}
                            {item.title}
                        </Link>
                    ))}
                </div>

                <div className="flex items-center gap-3">
                    <div className="hidden items-center gap-2 md:flex">
                        {rightNavItems.map((item) => (
                            <a
                                key={item.title}
                                href={toUrl(item.href)}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                                aria-label={item.title}
                            >
                                {item.icon && <item.icon className="h-5 w-5" />}
                            </a>
                        ))}
                    </div>

                    {auth.user && (
                        <Dropdown
                            inline
                            arrowIcon={false}
                            label={
                                <Avatar
                                    img={auth.user.avatar || undefined}
                                    placeholderInitials={getInitials(
                                        auth.user.name,
                                    )}
                                    rounded
                                    size="sm"
                                />
                            }
                        >
                            <UserMenuContent user={auth.user} />
                        </Dropdown>
                    )}
                </div>
            </Navbar>

            {breadcrumbs.length > 0 && (
                <div className="border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            )}

            <Drawer
                open={mobileOpen}
                onClose={() => setMobileOpen(false)}
                className="w-72"
            >
                <DrawerHeader
                    title="القائمة"
                        titleIcon={() => <AppLogoIcon className="h-5 w-5" />}
                    closeIcon={X}
                />
                <DrawerItems>
                    <div className="flex flex-col gap-2">
                        {mainNavItems.map((item) => (
                            <Link
                                key={item.title}
                                href={item.href}
                                onClick={() => setMobileOpen(false)}
                                className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                {item.icon && <item.icon className="h-4 w-4" />}
                                {item.title}
                            </Link>
                        ))}
                    </div>
                </DrawerItems>
            </Drawer>
        </>
    );
}
