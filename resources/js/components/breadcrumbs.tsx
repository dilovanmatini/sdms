import { Link } from '@inertiajs/react';
import { Breadcrumb, BreadcrumbItem } from 'flowbite-react';
import { Home } from 'lucide-react';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function Breadcrumbs({
    breadcrumbs,
}: {
    breadcrumbs: BreadcrumbItemType[];
}) {
    if (breadcrumbs.length === 0) {
        return null;
    }

    return (
        <Breadcrumb aria-label="مسار التنقل">
            {breadcrumbs.map((item, index) => {
                const isLast = index === breadcrumbs.length - 1;

                return (
                    <BreadcrumbItem
                        key={`${item.title}-${index}`}
                        icon={index === 0 ? Home : undefined}
                    >
                        {isLast ? (
                            item.title
                        ) : (
                            <Link
                                href={item.href}
                                className="text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {item.title}
                            </Link>
                        )}
                    </BreadcrumbItem>
                );
            })}
        </Breadcrumb>
    );
}
