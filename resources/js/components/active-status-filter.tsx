import { router } from '@inertiajs/react';
import { Label, Select } from 'flowbite-react';
import { cn } from '@/lib/utils';

export type ActiveStatusOption = {
    value: string;
    label: string;
};

type Props = {
    url: string;
    value: string;
    options: ActiveStatusOption[];
    search?: string;
    className?: string;
};

export function ActiveStatusFilter({
    url,
    value,
    options,
    search = '',
    className,
}: Props) {
    const apply = (isActive: string) => {
        const current =
            typeof window === 'undefined'
                ? {}
                : Object.fromEntries(
                      new URLSearchParams(window.location.search),
                  );

        router.get(
            url,
            {
                ...current,
                search: search || undefined,
                is_active: isActive || undefined,
                page: undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    return (
        <div className={cn('w-full sm:w-48', className)}>
            <Label htmlFor="is_active" className="mb-2 block">
                الحالة
            </Label>
            <Select
                id="is_active"
                value={value}
                onChange={(event) => apply(event.target.value)}
            >
                {options.map((option) => (
                    <option key={option.value || 'all'} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </Select>
        </div>
    );
}
