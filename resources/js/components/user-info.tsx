import { Avatar } from 'flowbite-react';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import type { User } from '@/types';

export function UserInfo({
    user,
    showEmail = false,
    showName = true,
    className,
}: {
    user: User;
    showEmail?: boolean;
    showName?: boolean;
    className?: string;
}) {
    const getInitials = useInitials();

    return (
        <div className={cn('flex min-w-0 items-center gap-2', className)}>
            <Avatar
                img={user.avatar || undefined}
                placeholderInitials={getInitials(user.name)}
                rounded
                size="sm"
                className="shrink-0"
                aria-hidden={showName}
            />
            {showName && (
                <div className="grid min-w-0 text-start text-sm leading-tight">
                    <span className="truncate font-medium text-gray-900 dark:text-white">
                        {user.name}
                    </span>
                    {showEmail && (
                        <span className="truncate text-xs text-gray-500 dark:text-gray-400">
                            {user.username}
                        </span>
                    )}
                </div>
            )}
        </div>
    );
}
