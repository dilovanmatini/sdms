import { Link } from '@inertiajs/react';
import { Breadcrumb, BreadcrumbItem } from 'flowbite-react';
import { Home } from 'lucide-react';
import { toUrl } from '@/lib/utils';
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
                        href={isLast ? undefined : toUrl(item.href)}
                        icon={index === 0 ? Home : undefined}
                    >
                        {isLast ? (
                            item.title
                        ) : (
                            <Link href={item.href}>{item.title}</Link>
                        )}
                    </BreadcrumbItem>
                );
            })}
        </Breadcrumb>
    );
}
