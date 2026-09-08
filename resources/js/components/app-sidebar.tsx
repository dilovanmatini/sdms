import { Link, usePage } from '@inertiajs/react';
import { Drawer, DrawerHeader, DrawerItems, Sidebar } from 'flowbite-react';
import {
    ChartColumn,
    FileText,
    LayoutGrid,
    Menu,
    Package,
    ScrollText,
    Settings,
    ShoppingCart,
    Tags,
    Truck,
    Users,
    Warehouse,
    Wallet,
    X,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import AppLogo from '@/components/app-logo';
import AppLogoIcon from '@/components/app-logo-icon';
import { NavFooter } from '@/components/nav-footer';
import { NavMain, type NavGroup } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import { index as categoriesIndex } from '@/routes/categories';
import { dashboard } from '@/routes';
import { index as distributorsIndex } from '@/routes/distributors';
import { index as inventoryIndex } from '@/routes/inventory';
import { index as paymentReceiptsIndex } from '@/routes/payment-receipts';
import { index as productsIndex } from '@/routes/products';
import { index as purchasesIndex } from '@/routes/purchases';
import { index as reportsIndex } from '@/routes/reports';
import { index as salesInvoicesIndex } from '@/routes/sales-invoices';
import { index as settingsIndex } from '@/routes/settings';
import { index as statementsIndex } from '@/routes/statements';
import { index as suppliersIndex } from '@/routes/suppliers';
import type { Auth, NavItem } from '@/types';

const allNavGroups: NavGroup[] = [
    {
        title: 'الرئيسية',
        items: [
            {
                title: 'الصفحة الرئيسية',
                href: dashboard(),
                icon: LayoutGrid,
                ability: 'view_dashboard',
            },
        ],
    },
    {
        title: 'المنتجات والمخزون',
        items: [
            {
                title: 'الأصناف',
                href: categoriesIndex(),
                icon: Tags,
                ability: 'manage_categories',
            },
            {
                title: 'المنتجات',
                href: productsIndex(),
                icon: Package,
                ability: 'manage_products',
            },
            {
                title: 'المخزون',
                href: inventoryIndex(),
                icon: Warehouse,
                ability: 'view_inventory',
            },
        ],
    },
    {
        title: 'المشتريات',
        items: [
            {
                title: 'الموردون',
                href: suppliersIndex(),
                icon: Truck,
                ability: 'manage_suppliers',
            },
            {
                title: 'المشتريات',
                href: purchasesIndex(),
                icon: ShoppingCart,
                ability: 'manage_purchases',
            },
        ],
    },
    {
        title: 'المبيعات',
        items: [
            {
                title: 'الموزعون',
                href: distributorsIndex(),
                icon: Users,
                ability: 'manage_distributors',
            },
            {
                title: 'فواتير المبيعات',
                href: salesInvoicesIndex(),
                icon: FileText,
                ability: 'manage_sales',
            },
            {
                title: 'سندات القبض',
                href: paymentReceiptsIndex(),
                icon: Wallet,
                ability: 'manage_receipts',
            },
            {
                title: 'كشف حساب العميل',
                href: statementsIndex(),
                icon: ScrollText,
                ability: 'view_statements',
            },
        ],
    },
    {
        title: 'التقارير',
        items: [
            {
                title: 'التقارير',
                href: reportsIndex(),
                icon: ChartColumn,
                ability: 'view_reports',
            },
        ],
    },
    {
        title: 'النظام',
        items: [
            {
                title: 'الإعدادات',
                href: settingsIndex(),
                icon: Settings,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const isMobile = useIsMobile();
    const [mobileOpen, setMobileOpen] = useState(false);
    const { sidebarOpen, auth } = usePage<{
        sidebarOpen: boolean;
        auth: Auth;
    }>().props;
    const [collapsed, setCollapsed] = useState(!sidebarOpen);

    const mainNavGroups = useMemo(() => {
        const abilities = auth.user?.abilities ?? [];

        return allNavGroups
            .map((group) => ({
                ...group,
                items: group.items.filter(
                    (item) =>
                        !item.ability || abilities.includes(item.ability),
                ),
            }))
            .filter((group) => group.items.length > 0);
    }, [auth.user?.abilities]);

    const toggleCollapsed = () => {
        const next = !collapsed;
        setCollapsed(next);
        document.cookie = `sidebar_state=${(!next).toString()};path=/;max-age=${60 * 60 * 24 * 7};SameSite=Lax`;
    };

    if (isMobile) {
        return (
            <>
                <button
                    type="button"
                    className="fixed top-3 inset-e-3 z-40 inline-flex items-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 focus:ring-0 focus-visible:shadow-focus focus:outline-none md:hidden dark:text-gray-400 dark:hover:bg-gray-700"
                    onClick={() => setMobileOpen(true)}
                    aria-label="فتح القائمة"
                >
                    <Menu className="h-5 w-5" />
                </button>
                <Drawer
                    open={mobileOpen}
                    onClose={() => setMobileOpen(false)}
                    className="w-72"
                    position="right"
                >
                    <DrawerHeader
                        title="القائمة"
                        titleIcon={() => <></>}
                        closeIcon={X}
                    />
                    <DrawerItems>
                        <Sidebar
                            aria-label="التنقل للجوال"
                            className="[&>div]:bg-transparent [&>div]:p-0"
                        >
                            <NavMain
                                groups={mainNavGroups}
                                onNavigate={() => setMobileOpen(false)}
                            />
                            <div className="mt-4 w-full">
                                <NavUser />
                            </div>
                        </Sidebar>
                    </DrawerItems>
                </Drawer>
            </>
        );
    }

    return (
        <Sidebar
            aria-label="قائمة التطبيق"
            collapsed={collapsed}
            collapseBehavior="collapse"
            className="sticky top-0 hidden h-screen shrink-0 border-e border-gray-200 md:block dark:border-gray-700"
            theme={{
                root: {
                    inner: cn(
                        'flex h-full w-full flex-col overflow-hidden bg-white py-4 dark:bg-gray-800',
                        collapsed ? 'px-2' : 'px-4',
                    ),
                },
                item: {
                    base: cn(
                        'flex items-center rounded-lg px-2 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700',
                        collapsed
                            ? 'justify-center'
                            : 'w-full justify-start',
                    ),
                    listItem: collapsed ? 'flex w-full justify-center' : '',
                },
                itemGroup: {
                    base: 'mt-3 space-y-0.5 border-t border-gray-200 pt-3 first:mt-0 first:border-t-0 first:pt-0 dark:border-gray-700',
                },
            }}
            applyTheme={{
                root: {
                    inner: 'replace',
                },
                item: {
                    base: 'replace',
                    listItem: 'replace',
                },
                itemGroup: {
                    base: 'replace',
                },
            }}
        >
            <div className="flex min-h-0 flex-1 flex-col gap-2">
                <div
                    className={cn(
                        'flex w-full items-center gap-1',
                        collapsed
                            ? 'flex-col justify-center'
                            : 'justify-between',
                    )}
                >
                    <Link
                        href={dashboard()}
                        prefetch
                        className="flex min-w-0 items-center justify-start rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        {collapsed ? (
                            <AppLogoIcon className="size-8" />
                        ) : (
                            <AppLogo />
                        )}
                    </Link>
                    <button
                        type="button"
                        onClick={toggleCollapsed}
                        className="shrink-0 rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                        aria-label={
                            collapsed ? 'توسيع القائمة' : 'طي القائمة'
                        }
                    >
                        <Menu className="h-5 w-5" />
                    </button>
                </div>

                <div className="min-h-0 flex-1 overflow-y-auto">
                    <NavMain groups={mainNavGroups} collapsed={collapsed} />
                </div>

                <div className="relative z-10 mt-auto space-y-2 border-t border-gray-200 pt-2 dark:border-gray-700">
                    <NavFooter items={footerNavItems} />
                    <NavUser collapsed={collapsed} />
                </div>
            </div>
        </Sidebar>
    );
}
