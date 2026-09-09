import { router } from '@inertiajs/react';
import { Label, TextInput } from 'flowbite-react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    url: string;
    initial?: string;
    placeholder?: string;
    className?: string;
    /** Extra query params preserved on submit (e.g. status filters). */
    params?: Record<string, string | undefined>;
};

export function SearchFilter({
    url,
    initial = '',
    placeholder = 'بحث...',
    className,
    params,
}: Props) {
    const [search, setSearch] = useState(initial);

    const submit = (event: FormEvent) => {
        event.preventDefault();

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
                ...params,
                search: search || undefined,
                page: undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    return (
        <form
            onSubmit={submit}
            className={cn('flex w-full max-w-md items-end gap-2', className)}
        >
            <div className="grow">
                <Label htmlFor="search" className="sr-only">
                    بحث
                </Label>
                <TextInput
                    id="search"
                    type="search"
                    icon={Search}
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder={placeholder}
                />
            </div>
        </form>
    );
}
