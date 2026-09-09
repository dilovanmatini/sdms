import { SidebarItem, SidebarItemGroup, SidebarItems } from 'flowbite-react';
import type { ComponentProps, FC } from 'react';
import { cn, toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

export function NavFooter({
    items,
    className,
}: {
    items: NavItem[];
    className?: string;
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <SidebarItems className={cn(className)}>
            <SidebarItemGroup>
                {items.map((item) => {
                    const icon = item.icon as
                        FC<ComponentProps<'svg'>> | undefined;

                    return (
                        <SidebarItem
                            key={item.title}
                            as="a"
                            href={toUrl(item.href)}
                            icon={icon}
                            onClick={(event) => {
                                event.preventDefault();
                                window.open(
                                    toUrl(item.href),
                                    '_blank',
                                    'noopener,noreferrer',
                                );
                            }}
                        >
                            {item.title}
                        </SidebarItem>
                    );
                })}
            </SidebarItemGroup>
        </SidebarItems>
    );
}
