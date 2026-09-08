import { Breadcrumbs } from '@/components/breadcrumbs';
import { DistributorQuickSearch } from '@/components/distributor-quick-search';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-3 pe-14 sm:px-4 md:px-6 md:pe-6 dark:border-gray-700 dark:bg-gray-800">
            <div className="hidden min-w-0 flex-1 overflow-hidden sm:block">
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <div className="min-w-0 flex-1 sm:ms-auto sm:w-72 sm:flex-none md:w-80 lg:w-96">
                <DistributorQuickSearch />
            </div>
        </header>
    );
}
