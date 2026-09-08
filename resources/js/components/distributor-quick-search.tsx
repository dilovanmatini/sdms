import { Link, usePage } from '@inertiajs/react';
import {
    Button,
    Modal,
    ModalBody,
    ModalHeader,
    Spinner,
    TextInput,
} from 'flowbite-react';
import {
    EllipsisVertical,
    FileText,
    Pencil,
    ScrollText,
    Wallet,
    Search,
    Users,
} from 'lucide-react';
import { useEffect, useId, useLayoutEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import type { SearchableSelectOption } from '@/components/searchable-select';
import { lookupQuery, useLookupOptions } from '@/hooks/use-lookup-options';
import { cn } from '@/lib/utils';
import { createEdit as distributorsCreateEdit } from '@/routes/distributors';
import { distributors as distributorLookups } from '@/routes/lookups';
import { createEdit as paymentReceiptsCreateEdit } from '@/routes/payment-receipts';
import {
    createEdit as salesInvoicesCreateEdit,
    index as salesInvoicesIndex,
} from '@/routes/sales-invoices';
import { index as statementsIndex } from '@/routes/statements';

type MenuPosition = {
    top: number;
    left: number;
};

type Shortcut = {
    key: string;
    label: string;
    href: string;
    icon: typeof FileText;
    ability: string;
};

const LOOKUP_ABILITIES = [
    'manage_distributors',
    'manage_sales',
    'manage_receipts',
    'view_statements',
] as const;

function distributorShortcuts(distributorId: number): Shortcut[] {
    return [
        {
            key: 'sales-invoice',
            label: 'فاتورة مبيعات جديدة',
            href: salesInvoicesCreateEdit.url(
                {},
                { query: { distributor_id: distributorId } },
            ),
            icon: FileText,
            ability: 'manage_sales',
        },
        {
            key: 'payment-receipt',
            label: 'سند قبض جديد',
            href: paymentReceiptsCreateEdit.url(
                {},
                { query: { distributor_id: distributorId } },
            ),
            icon: Wallet,
            ability: 'manage_receipts',
        },
        {
            key: 'statement',
            label: 'كشف حساب الموزع',
            href: statementsIndex.url({
                query: { distributor_id: distributorId },
            }),
            icon: ScrollText,
            ability: 'view_statements',
        },
        {
            key: 'sales-list',
            label: 'فواتير المبيعات',
            href: salesInvoicesIndex.url({
                query: { distributor_id: distributorId },
            }),
            icon: FileText,
            ability: 'manage_sales',
        },
        {
            key: 'edit',
            label: 'تعديل الموزع',
            href: distributorsCreateEdit.url(distributorId),
            icon: Pencil,
            ability: 'manage_distributors',
        },
    ];
}

function ResultShortcuts({
    distributor,
    abilities,
    onNavigate,
}: {
    distributor: SearchableSelectOption;
    abilities: string[];
    onNavigate: () => void;
}) {
    const [open, setOpen] = useState(false);
    const [position, setPosition] = useState<MenuPosition | null>(null);
    const buttonRef = useRef<HTMLButtonElement>(null);
    const menuRef = useRef<HTMLDivElement>(null);
    const menuId = useId();
    const shortcuts = useMemo(
        () =>
            distributorShortcuts(Number(distributor.value)).filter((item) =>
                abilities.includes(item.ability),
            ),
        [abilities, distributor.value],
    );

    useLayoutEffect(() => {
        if (!open || !buttonRef.current) {
            return;
        }

        const updatePosition = () => {
            const button = buttonRef.current;
            const menu = menuRef.current;

            if (!button) {
                return;
            }

            const rect = button.getBoundingClientRect();
            const menuWidth = menu?.offsetWidth ?? 224;
            const menuHeight = menu?.offsetHeight ?? shortcuts.length * 40;
            const gap = 4;
            const viewportPadding = 8;

            let top = rect.bottom + gap;
            let left = rect.right - menuWidth;

            if (top + menuHeight > window.innerHeight - viewportPadding) {
                top = Math.max(
                    viewportPadding,
                    rect.top - menuHeight - gap,
                );
            }

            left = Math.min(
                Math.max(viewportPadding, left),
                window.innerWidth - menuWidth - viewportPadding,
            );

            setPosition({ top, left });
        };

        updatePosition();

        window.addEventListener('resize', updatePosition);
        window.addEventListener('scroll', updatePosition, true);

        return () => {
            window.removeEventListener('resize', updatePosition);
            window.removeEventListener('scroll', updatePosition, true);
        };
    }, [open, shortcuts.length]);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onPointerDown = (event: PointerEvent) => {
            const target = event.target as Node;

            if (
                buttonRef.current?.contains(target) ||
                menuRef.current?.contains(target)
            ) {
                return;
            }

            setOpen(false);
        };

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        document.addEventListener('pointerdown', onPointerDown);
        document.addEventListener('keydown', onKeyDown);

        return () => {
            document.removeEventListener('pointerdown', onPointerDown);
            document.removeEventListener('keydown', onKeyDown);
        };
    }, [open]);

    if (shortcuts.length === 0) {
        return null;
    }

    return (
        <>
            <Button
                ref={buttonRef}
                type="button"
                color="light"
                size="sm"
                aria-expanded={open}
                aria-haspopup="menu"
                aria-controls={menuId}
                aria-label={`اختصارات ${distributor.label}`}
                onClick={() => setOpen((current) => !current)}
                className="shrink-0 p-2!"
            >
                <EllipsisVertical className="size-4" />
            </Button>

            {open &&
                createPortal(
                    <div
                        ref={menuRef}
                        id={menuId}
                        role="menu"
                        style={
                            position
                                ? {
                                      position: 'fixed',
                                      top: position.top,
                                      left: position.left,
                                  }
                                : {
                                      position: 'fixed',
                                      top: -9999,
                                      left: -9999,
                                      visibility: 'hidden',
                                  }
                        }
                        className="z-60 min-w-56 rounded-lg border border-gray-200 bg-white py-1 shadow dark:border-gray-600 dark:bg-gray-700"
                    >
                        {shortcuts.map((shortcut) => (
                            <Link
                                key={shortcut.key}
                                href={shortcut.href}
                                role="menuitem"
                                onClick={() => {
                                    setOpen(false);
                                    onNavigate();
                                }}
                                className="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                <shortcut.icon className="size-4 shrink-0 text-gray-500 dark:text-gray-400" />
                                {shortcut.label}
                            </Link>
                        ))}
                    </div>,
                    document.body,
                )}
        </>
    );
}

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
    const searchTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(
        null,
    );

    const { options, loading, onSearch } = useLookupOptions({
        buildUrl: (search) =>
            distributorLookups.url(lookupQuery(search, { limit: 15 })),
    });

    useEffect(() => {
        if (!canSearch) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
                event.preventDefault();
                setOpen(true);
            }
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [canSearch]);

    useEffect(() => {
        if (!open) {
            return;
        }

        setHasSearched(true);
        onSearch('');
        // Intentionally only when the modal opens.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

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
        const parts = [
            option.meta?.contact_person,
            option.meta?.phone,
        ].filter((part): part is string => Boolean(part));

        return parts.length > 0 ? parts.join(' · ') : null;
    };

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen(true)}
                aria-label="بحث عن موزع"
                data-test="distributor-quick-search"
                className={cn(
                    'flex w-full items-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-start text-sm text-gray-500',
                    'hover:bg-gray-100 focus:border-gray-300 focus:outline-none focus:ring-0 focus-visible:shadow-focus',
                    'dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 dark:focus:border-gray-600',
                )}
            >
                <Search className="size-4 shrink-0 text-gray-400" />
                <span className="min-w-0 flex-1 truncate">
                    بحث عن موزع...
                </span>
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
                                        <ResultShortcuts
                                            distributor={option}
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
