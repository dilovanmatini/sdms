import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

export type SettingsNavItem = NavItem & {
    ability?: string;
    icon: LucideIcon;
};

type SettingsSubNavProps = {
    items: SettingsNavItem[];
    ariaLabel: string;
    /**
     * When set, only that item uses exact URL matching; other items
     * match the current URL or any nested child path.
     */
    exactHref?: string;
};

/** Shared active + hover appearance so both states match. */
const selectedAppearance =
    'bg-primary-50 text-primary-700 dark:bg-gray-700 dark:text-primary-300';

export function SettingsSubNav({
    items,
    ariaLabel,
    exactHref,
}: SettingsSubNavProps) {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <nav
            className="flex flex-wrap gap-1 rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800"
            aria-label={ariaLabel}
        >
            {items.map((item) => {
                const href = toUrl(item.href);
                const active =
                    exactHref !== undefined && href === exactHref
                        ? isCurrentUrl(item.href)
                        : isCurrentOrParentUrl(item.href);
                const Icon = item.icon;

                return (
                    <Link
                        key={href}
                        href={href}
                        aria-current={active ? 'page' : undefined}
                        className={cn(
                            'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                            'hover:bg-primary-50 hover:text-primary-700 dark:hover:bg-gray-700 dark:hover:text-primary-300',
                            active
                                ? selectedAppearance
                                : 'text-gray-600 dark:text-gray-300',
                        )}
                    >
                        <Icon className="h-4 w-4 shrink-0" aria-hidden />
                        {item.title}
                    </Link>
                );
            })}
        </nav>
    );
}
