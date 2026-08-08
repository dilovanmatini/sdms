import { Link } from '@inertiajs/react';
import { Button } from 'flowbite-react';
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
        'current_page' | 'last_page' | 'prev_page_url' | 'next_page_url' | 'from' | 'to' | 'total'
    >;
};

export function PaginationLinks({ meta }: Props) {
    if (meta.last_page <= 1) {
        return null;
    }

    return (
        <div className="flex flex-col items-center justify-between gap-3 sm:flex-row">
            <p className="text-sm text-gray-500 dark:text-gray-400">
                عرض {meta.from ?? 0} إلى {meta.to ?? 0} من أصل {meta.total}
            </p>
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
