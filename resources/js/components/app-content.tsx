import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';
import type { AppVariant } from '@/types';

type Props = ComponentProps<'main'> & {
    variant?: AppVariant;
};

export function AppContent({
    variant = 'sidebar',
    children,
    className,
    ...props
}: Props) {
    return (
        <main
            className={cn(
                variant === 'sidebar'
                    ? 'flex min-h-screen flex-1 flex-col'
                    : 'mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4',
                className,
            )}
            {...props}
        >
            {children}
        </main>
    );
}
