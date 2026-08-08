import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { Button } from 'flowbite-react';
import type { ReactNode } from 'react';
import Heading from '@/components/heading';
import { toUrl } from '@/lib/utils';

type Props = {
    title: string;
    description?: string;
    actionHref?: NonNullable<InertiaLinkProps['href']>;
    actionLabel?: string;
    children?: ReactNode;
};

export function PageHeader({
    title,
    description,
    actionHref,
    actionLabel,
    children,
}: Props) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <Heading title={title} description={description} />
            <div className="flex items-center gap-2">
                {children}
                {actionHref && actionLabel && (
                    <Button as={Link} href={toUrl(actionHref)}>
                        {actionLabel}
                    </Button>
                )}
            </div>
        </div>
    );
}
