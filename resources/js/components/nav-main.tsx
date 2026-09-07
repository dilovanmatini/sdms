import { Link } from '@inertiajs/react';
import { SidebarItem, SidebarItemGroup, SidebarItems } from 'flowbite-react';
import type { ComponentProps, FC } from 'react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as settingsIndex } from '@/routes/settings';
import type { NavItem } from '@/types';

export type NavItemWithAbility = NavItem & {
    ability?: string;
};

export type NavGroup = {
    title: string;
    items: NavItemWithAbility[];
};

const userSettingsPrefixes = [
    '/settings/profile',
    '/settings/security',
    '/settings/appearance',
    '/settings/password',
];

function isUserSettingsPath(path: string): boolean {
    return userSettingsPrefixes.some(
        (prefix) => path === prefix || path.startsWith(`${prefix}/`),
    );
}

function isSystemSettingsPath(path: string): boolean {
    if (!path.startsWith('/settings')) {
        return false;
    }

    return !isUserSettingsPath(path);
}

function isItemActive(
    item: NavItemWithAbility,
    currentUrl: string,
    isCurrentUrl: ReturnType<typeof useCurrentUrl>['isCurrentUrl'],
): boolean {
    const href = toUrl(item.href);
    const dashboardUrl = toUrl(dashboard());

    if (href === dashboardUrl) {
        return item.ability === 'view_dashboard' && isCurrentUrl(dashboard());
    }

    if (href === toUrl(settingsIndex())) {
        return isSystemSettingsPath(currentUrl);
    }

    return isCurrentUrl(item.href);
}

export function NavMain({
    groups = [],
    onNavigate,
}: {
    groups: NavGroup[];
    collapsed?: boolean;
    onNavigate?: () => void;
}) {
    const { currentUrl, isCurrentUrl } = useCurrentUrl();

    const visibleGroups = groups.filter((group) => group.items.length > 0);

    return (
        <SidebarItems>
            {visibleGroups.map((group) => (
                <SidebarItemGroup key={group.title}>
                    {group.items.map((item) => {
                        const icon = item.icon as
                            | FC<ComponentProps<'svg'>>
                            | undefined;
                        const href = toUrl(item.href);

                        return (
                            <SidebarItem
                                key={item.title}
                                as={Link}
                                href={href}
                                icon={icon}
                                active={isItemActive(
                                    item,
                                    currentUrl,
                                    isCurrentUrl,
                                )}
                                onClick={onNavigate}
                            >
                                {item.title}
                            </SidebarItem>
                        );
                    })}
                </SidebarItemGroup>
            ))}
        </SidebarItems>
    );
}
