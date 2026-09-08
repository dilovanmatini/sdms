import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type FormCardProps = {
    title: string;
    description?: string;
    icon?: LucideIcon;
    actions?: ReactNode;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
};

export function FormCard({
    title,
    description,
    icon: Icon,
    actions,
    children,
    className,
    contentClassName,
}: FormCardProps) {
    return (
        <div
            className={cn(
                'w-full rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800',
                className,
            )}
        >
            <div className="flex flex-col gap-4 border-b border-gray-200 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-700">
                <div className="flex items-center gap-3">
                    {Icon && (
                        <div className="rounded-full bg-primary-50 p-2.5 text-primary-700 dark:bg-gray-700 dark:text-primary-300">
                            <Icon className="h-5 w-5" aria-hidden />
                        </div>
                    )}
                    <div className="min-w-0 space-y-0.5">
                        <h2 className="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                            {title}
                        </h2>
                        {description && (
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {description}
                            </p>
                        )}
                    </div>
                </div>
                {actions && (
                    <div className="flex shrink-0 flex-wrap items-center gap-2">
                        {actions}
                    </div>
                )}
            </div>
            <div className={cn('px-4 py-5 sm:px-6', contentClassName)}>
                {children}
            </div>
        </div>
    );
}

export function FormActions({
    children,
    secondary,
    className,
}: {
    children?: ReactNode;
    secondary?: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 pt-5 dark:border-gray-700',
                className,
            )}
        >
            <div className="flex flex-wrap items-center gap-2">{children}</div>
            {secondary != null && (
                <div className="flex flex-wrap items-center gap-2">
                    {secondary}
                </div>
            )}
        </div>
    );
}
