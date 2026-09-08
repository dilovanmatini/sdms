import { Link, router } from '@inertiajs/react';
import { Button, Select } from 'flowbite-react';
import { useEffect } from 'react';
import type { ChangeEvent } from 'react';
import {
    DATAGRID_PER_PAGE_OPTIONS,
    getStoredDatagridPerPage,
    setDatagridPerPage,
} from '@/lib/datagrid-per-page';
import type { DatagridPerPageOption } from '@/lib/datagrid-per-page';
import { toUrl } from '@/lib/utils';

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};

type Props = {
    meta: Pick<
        Paginated<unknown>,
        | 'current_page'
        | 'last_page'
        | 'per_page'
        | 'prev_page_url'
        | 'next_page_url'
        | 'from'
        | 'to'
        | 'total'
    >;
    /** Unique key used to persist per-page preference for this datagrid. */
    storageKey: string;
};

const currentQuery = (): Record<string, string> => {
    if (typeof window === 'undefined') {
        return {};
    }

    return Object.fromEntries(new URLSearchParams(window.location.search));
};

export function PaginationLinks({ meta, storageKey }: Props) {
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const stored = getStoredDatagridPerPage(storageKey);

        if (params.has('per_page')) {
            if (
                (DATAGRID_PER_PAGE_OPTIONS as readonly number[]).includes(
                    meta.per_page,
                )
            ) {
                setDatagridPerPage(
                    storageKey,
                    meta.per_page as DatagridPerPageOption,
                );
            }

            return;
        }

        if (stored === null) {
            if (
                (DATAGRID_PER_PAGE_OPTIONS as readonly number[]).includes(
                    meta.per_page,
                )
            ) {
                setDatagridPerPage(
                    storageKey,
                    meta.per_page as DatagridPerPageOption,
                );
            }

            return;
        }

        if (stored === meta.per_page) {
            return;
        }

        setDatagridPerPage(storageKey, stored);
        router.get(
            window.location.pathname,
            {
                ...currentQuery(),
                per_page: stored,
                page: undefined,
            },
            {
                replace: true,
                preserveState: true,
                preserveScroll: true,
            },
        );
    }, [meta.per_page, storageKey]);

    if (meta.total <= 0) {
        return null;
    }

    const onPerPageChange = (event: ChangeEvent<HTMLSelectElement>) => {
        const perPage = Number(event.target.value) as DatagridPerPageOption;

        if (!(DATAGRID_PER_PAGE_OPTIONS as readonly number[]).includes(perPage)) {
            return;
        }

        setDatagridPerPage(storageKey, perPage);
        router.get(
            window.location.pathname,
            {
                ...currentQuery(),
                per_page: perPage,
                page: undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <div className="flex flex-col items-center justify-between gap-3 sm:flex-row">
            <div className="flex flex-col items-center gap-3 sm:flex-row">
            <div className="flex items-center gap-2">
                    <Select
                        id={`per-page-${storageKey}`}
                        sizing="sm"
                        value={String(meta.per_page)}
                        onChange={onPerPageChange}
                        className="w-16"
                        aria-label="عدد السجلات لكل صفحة"
                    >
                        {DATAGRID_PER_PAGE_OPTIONS.map((option) => (
                            <option key={option} value={option}>
                                {option}
                            </option>
                        ))}
                    </Select>
                </div>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                    عرض {meta.from ?? 0} إلى {meta.to ?? 0} من أصل {meta.total}
                </p>
            </div>
            <div className="flex gap-2">
                {meta.prev_page_url ? (
                    <Button
                        as={Link}
                        href={toUrl(meta.prev_page_url)}
                        color="light"
                        size="sm"
                        preserveScroll
                    >
                        السابق
                    </Button>
                ) : (
                    <Button color="light" size="sm" disabled>
                        السابق
                    </Button>
                )}
                <span className="flex items-center text-sm text-gray-600 dark:text-gray-300">
                    {meta.current_page} / {meta.last_page}
                </span>
                {meta.next_page_url ? (
                    <Button
                        as={Link}
                        href={toUrl(meta.next_page_url)}
                        color="light"
                        size="sm"
                        preserveScroll
                    >
                        التالي
                    </Button>
                ) : (
                    <Button color="light" size="sm" disabled>
                        التالي
                    </Button>
                )}
            </div>
        </div>
    );
}
