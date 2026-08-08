import { Button } from 'flowbite-react';
import type { LucideIcon } from 'lucide-react';
import { Monitor, Moon, Sun } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

export default function AppearanceToggleTab({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { appearance, updateAppearance } = useAppearance();

    const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
        { value: 'light', icon: Sun, label: 'فاتح' },
        { value: 'dark', icon: Moon, label: 'داكن' },
        { value: 'system', icon: Monitor, label: 'النظام' },
    ];

    return (
        <div
            className={cn(
                'inline-flex gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-800',
                className,
            )}
            {...props}
        >
            {tabs.map(({ value, icon: Icon, label }) => (
                <Button
                    key={value}
                    size="sm"
                    color={appearance === value ? 'light' : 'gray'}
                    onClick={() => updateAppearance(value)}
                    className={cn(
                        'gap-1.5 border-0',
                        appearance === value
                            ? 'bg-white shadow-sm dark:bg-gray-700'
                            : 'bg-transparent text-gray-500',
                    )}
                >
                    <Icon className="h-4 w-4" />
                    <span className="text-sm">{label}</span>
                </Button>
            ))}
        </div>
    );
}
