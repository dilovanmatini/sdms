import { usePage } from '@inertiajs/react';
import {
    Modal,
    ModalBody,
    ModalHeader,
    Spinner,
    TextInput,
} from 'flowbite-react';
import { Search, Users } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { DistributorActionsMenu } from '@/components/distributor-actions-menu';
import type { SearchableSelectOption } from '@/components/searchable-select';
import { lookupQuery, useLookupOptions } from '@/hooks/use-lookup-options';
import { cn } from '@/lib/utils';
import { distributors as distributorLookups } from '@/routes/lookups';

const LOOKUP_ABILITIES = [
    'manage_distributors',
    'manage_sales',
    'manage_opening_balances',
    'manage_receipts',
    'view_statements',
] as const;

export function DistributorQuickSearch() {
    const { auth } = usePage().props;
    const abilities = auth.user?.abilities ?? [];
    const canSearch = LOOKUP_ABILITIES.some((ability) =>
        abilities.includes(ability),
    );

    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [hasSearched, setHasSearched] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);
    const searchTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const { options, loading, onSearch } = useLookupOptions({
        buildUrl: (search) =>
            distributorLookups.url(lookupQuery(search, { limit: 15 })),
    });

    const openModal = useCallback(() => {
        setOpen(true);
        setHasSearched(true);
        onSearch('');
    }, [onSearch]);

    useEffect(() => {
        if (!canSearch) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
                event.preventDefault();
                openModal();
            }
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [canSearch, openModal]);

    useEffect(() => {
        return () => {
            if (searchTimeoutRef.current) {
                clearTimeout(searchTimeoutRef.current);
            }
        };
    }, []);

    if (!canSearch) {
        return null;
    }

    const close = () => {
        setOpen(false);
        setQuery('');
        setHasSearched(false);
    };

    const handleQueryChange = (value: string) => {
        setQuery(value);

        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }

        searchTimeoutRef.current = setTimeout(() => {
            setHasSearched(true);
            onSearch(value.trim());
        }, 300);
    };

    const subtitle = (option: SearchableSelectOption): string | null => {
        const parts = [option.meta?.contact_person, option.meta?.phone].filter(
            (part): part is string => Boolean(part),
        );

        return parts.length > 0 ? parts.join(' · ') : null;
    };

    return (
        <>
            <button
                type="button"
                onClick={openModal}
                aria-label="بحث عن موزع"
                data-test="distributor-quick-search"
                className={cn(
                    'flex w-full items-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-start text-sm text-gray-500',
                    'hover:bg-gray-100 focus:border-gray-300 focus:ring-0 focus:outline-none focus-visible:shadow-focus',
                    'dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 dark:focus:border-gray-600',
                )}
            >
                <Search className="size-4 shrink-0 text-gray-400" />
                <span className="min-w-0 flex-1 truncate">بحث عن موزع...</span>
                <kbd className="ms-auto hidden shrink-0 rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-gray-500 sm:inline dark:border-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    ⌘K
                </kbd>
            </button>

            <Modal
                show={open}
                onClose={close}
                size="2xl"
                dismissible
                position="top-center"
                initialFocus={inputRef}
                theme={{
                    content: {
                        base: 'relative h-full w-full p-2 sm:h-auto sm:p-4',
                        inner: 'relative flex max-h-[calc(100dvh-1rem)] flex-col rounded-lg bg-white shadow dark:bg-gray-700 sm:max-h-[90dvh]',
                    },
                    header: {
                        base: 'flex items-start justify-between rounded-t border-b border-gray-200 p-4 sm:p-5 dark:border-gray-600',
                        title: 'text-base font-medium text-gray-900 sm:text-xl dark:text-white',
                    },
                    body: {
                        base: 'flex-1 overflow-auto p-4 sm:p-6',
                    },
                }}
            >
                <ModalHeader>
                    <span className="inline-flex items-center gap-2">
                        <Users className="size-5 shrink-0" />
                        البحث عن الموزعين
                    </span>
                </ModalHeader>
                <ModalBody className="space-y-4">
                    <TextInput
                        ref={inputRef}
                        type="search"
                        icon={Search}
                        value={query}
                        onChange={(event) =>
                            handleQueryChange(event.target.value)
                        }
                        placeholder="ابحث بالاسم أو الهاتف أو جهة الاتصال..."
                        className="w-full"
                        sizing="md"
                    />

                    <div
                        className="max-h-[min(24rem,55dvh)] overflow-y-auto rounded-lg border border-gray-200 sm:max-h-80 dark:border-gray-600"
                        role="listbox"
                        aria-label="نتائج البحث"
                    >
                        {loading && (
                            <div className="flex items-center justify-center gap-2 px-4 py-8 text-sm text-gray-500">
                                <Spinner size="sm" />
                                جاري البحث...
                            </div>
                        )}

                        {!loading && hasSearched && options.length === 0 && (
                            <p className="px-4 py-8 text-center text-sm text-gray-500">
                                لا توجد نتائج
                            </p>
                        )}

                        {!loading &&
                            options.map((option) => {
                                const detail = subtitle(option);

                                return (
                                    <div
                                        key={String(option.value)}
                                        role="option"
                                        aria-selected={false}
                                        className="flex items-center gap-3 border-b border-gray-100 px-3 py-3 last:border-b-0 sm:py-2.5 dark:border-gray-700"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-medium text-gray-900 dark:text-white">
                                                {option.label}
                                            </p>
                                            {detail && (
                                                <p className="truncate text-xs text-gray-500 dark:text-gray-400">
                                                    {detail}
                                                </p>
                                            )}
                                        </div>
                                        <DistributorActionsMenu
                                            distributorId={Number(option.value)}
                                            distributorLabel={option.label}
                                            abilities={abilities}
                                            onNavigate={close}
                                        />
                                    </div>
                                );
                            })}
                    </div>
                </ModalBody>
            </Modal>
        </>
    );
}
