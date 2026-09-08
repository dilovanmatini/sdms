import { Spinner, TextInput } from 'flowbite-react';
import { Check, ChevronDown, Search } from 'lucide-react';
import {
    useEffect,
    useId,
    useMemo,
    useRef,
    useState,
} from 'react';
import type { KeyboardEvent } from 'react';
import { cn } from '@/lib/utils';

export type SearchableSelectOption = {
    value: string | number;
    label: string;
    meta?: Record<string, string | number | null>;
};

type Props = {
    id?: string;
    name: string;
    options: SearchableSelectOption[];
    defaultValue?: string | number | null;
    value?: string | number | null;
    onChange?: (
        value: string,
        option?: SearchableSelectOption | null,
    ) => void;
    placeholder?: string;
    searchPlaceholder?: string;
    required?: boolean;
    disabled?: boolean;
    className?: string;
    /**
     * Remote/AJAX search. When provided, client-side filtering is off unless
     * `filterLocal` is explicitly set to true. Debounced while the menu is open.
     */
    onSearch?: (query: string) => void;
    searchDebounceMs?: number;
    loading?: boolean;
    /** Client-side filter. Defaults to true when `onSearch` is omitted. */
    filterLocal?: boolean;
    emptyMessage?: string;
};

function toStringValue(value: string | number | null | undefined): string {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value);
}

function findLabel(
    options: SearchableSelectOption[],
    selectedValue: string,
): string {
    return (
        options.find((option) => String(option.value) === selectedValue)
            ?.label ?? ''
    );
}

export function SearchableSelect({
    id,
    name,
    options,
    defaultValue = '',
    value,
    onChange,
    placeholder = 'اختر...',
    searchPlaceholder = 'بحث...',
    required = false,
    disabled = false,
    className,
    onSearch,
    searchDebounceMs = 300,
    loading = false,
    filterLocal,
    emptyMessage = 'لا توجد نتائج',
}: Props) {
    const reactId = useId();
    const fieldId = id ?? `searchable-select-${reactId}`;
    const listboxId = `${fieldId}-listbox`;
    const searchInputId = `${fieldId}-search`;
    const shouldFilterLocal = filterLocal ?? !onSearch;

    const rootRef = useRef<HTMLDivElement>(null);
    const searchRef = useRef<HTMLInputElement>(null);

    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [internalValue, setInternalValue] = useState(
        toStringValue(defaultValue),
    );
    const [cachedLabel, setCachedLabel] = useState(() =>
        findLabel(options, toStringValue(defaultValue)),
    );
    const [activeIndex, setActiveIndex] = useState(0);

    const isControlled = value !== undefined;
    const selectedValue = isControlled
        ? toStringValue(value)
        : internalValue;

    const optionLabel = findLabel(options, selectedValue);
    const selectedLabel = optionLabel !== '' ? optionLabel : cachedLabel;

    const visibleOptions = useMemo(() => {
        if (!shouldFilterLocal || query.trim() === '') {
            return options;
        }

        const normalized = query.trim().toLocaleLowerCase('ar');

        return options.filter((option) =>
            option.label.toLocaleLowerCase('ar').includes(normalized),
        );
    }, [options, query, shouldFilterLocal]);

    useEffect(() => {
        if (!open) {
            return;
        }

        const frame = window.requestAnimationFrame(() => {
            searchRef.current?.focus();
        });

        return () => window.cancelAnimationFrame(frame);
    }, [open]);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onPointerDown = (event: MouseEvent) => {
            if (
                rootRef.current &&
                !rootRef.current.contains(event.target as Node)
            ) {
                setOpen(false);
                setQuery('');
            }
        };

        document.addEventListener('mousedown', onPointerDown);

        return () => document.removeEventListener('mousedown', onPointerDown);
    }, [open]);

    useEffect(() => {
        if (!open || !onSearch) {
            return;
        }

        // Preload / refresh immediately when opening with an empty query;
        // debounce only while the user is typing a search.
        const delay = query.trim() === '' ? 0 : searchDebounceMs;
        const timer = window.setTimeout(() => {
            onSearch(query);
        }, delay);

        return () => window.clearTimeout(timer);
    }, [open, onSearch, query, searchDebounceMs]);

    const selectOption = (option: SearchableSelectOption) => {
        const nextValue = String(option.value);

        if (!isControlled) {
            setInternalValue(nextValue);
        }

        setCachedLabel(option.label);
        onChange?.(nextValue, option);
        setQuery('');
        setOpen(false);
    };

    const closeMenu = () => {
        setOpen(false);
        setQuery('');
    };

    const openMenu = () => {
        setActiveIndex(0);
        setOpen(true);
    };

    const onTriggerKeyDown = (event: KeyboardEvent<HTMLButtonElement>) => {
        if (disabled) {
            return;
        }

        if (
            event.key === 'ArrowDown' ||
            event.key === 'Enter' ||
            event.key === ' '
        ) {
            event.preventDefault();
            openMenu();
        }
    };

    const onSearchKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeMenu();

            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveIndex((current) =>
                visibleOptions.length === 0
                    ? 0
                    : Math.min(current + 1, visibleOptions.length - 1),
            );

            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveIndex((current) => Math.max(current - 1, 0));

            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            const option = visibleOptions[activeIndex];

            if (option) {
                selectOption(option);
            }
        }
    };

    const displayLabel =
        selectedValue !== '' && selectedLabel !== ''
            ? selectedLabel
            : placeholder;

    return (
        <div ref={rootRef} className={cn('relative w-full', className)}>
            <select
                id={fieldId}
                name={name}
                required={required}
                disabled={disabled}
                value={selectedValue}
                onChange={(event) => {
                    const nextValue = event.target.value;
                    const option = options.find(
                        (item) => String(item.value) === nextValue,
                    );

                    if (option) {
                        selectOption(option);

                        return;
                    }

                    if (!isControlled) {
                        setInternalValue(nextValue);
                    }

                    onChange?.(nextValue, null);
                }}
                tabIndex={-1}
                aria-hidden="true"
                className="pointer-events-none absolute h-px w-px opacity-0"
            >
                <option value="">{placeholder}</option>
                {selectedValue !== '' &&
                    selectedLabel !== '' &&
                    !options.some(
                        (option) => String(option.value) === selectedValue,
                    ) && (
                        <option value={selectedValue}>{selectedLabel}</option>
                    )}
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

            <button
                type="button"
                disabled={disabled}
                aria-haspopup="listbox"
                aria-expanded={open}
                aria-controls={listboxId}
                className={cn(
                    'relative flex min-h-10.5 w-full cursor-pointer items-center justify-between gap-2 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-start text-sm leading-5',
                    'focus:border-gray-300 focus:ring-0 focus:shadow-focus focus:outline-none',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    'dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-gray-600',
                    selectedValue !== '' && selectedLabel !== ''
                        ? 'text-gray-900 dark:text-white'
                        : 'text-gray-500 dark:text-gray-400',
                )}
                onClick={() => {
                    if (disabled) {
                        return;
                    }

                    if (open) {
                        closeMenu();

                        return;
                    }

                    openMenu();
                }}
                onKeyDown={onTriggerKeyDown}
            >
                <span className="min-w-0 flex-1 truncate">{displayLabel}</span>
                <ChevronDown
                    aria-hidden
                    className="size-4 shrink-0 text-gray-500 dark:text-gray-400"
                />
            </button>

            {open && (
                <div
                    className="absolute inset-x-0 z-20 mt-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow dark:border-gray-600 dark:bg-gray-700"
                    role="presentation"
                >
                    <div className="border-b border-gray-100 p-2 dark:border-gray-600">
                        <label htmlFor={searchInputId} className="sr-only">
                            بحث
                        </label>
                        <TextInput
                            ref={searchRef}
                            id={searchInputId}
                            type="search"
                            sizing="sm"
                            icon={Search}
                            value={query}
                            autoComplete="off"
                            placeholder={searchPlaceholder}
                            onChange={(event) => {
                                setQuery(event.target.value);
                                setActiveIndex(0);
                            }}
                            onKeyDown={onSearchKeyDown}
                        />
                    </div>

                    <ul
                        id={listboxId}
                        role="listbox"
                        aria-labelledby={fieldId}
                        className="max-h-48 overflow-y-auto py-1"
                    >
                        {loading ? (
                            <li
                                role="presentation"
                                className="flex items-center justify-center gap-2 px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <Spinner size="sm" />
                                جاري البحث...
                            </li>
                        ) : visibleOptions.length === 0 ? (
                            <li
                                role="presentation"
                                className="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {emptyMessage}
                            </li>
                        ) : (
                            visibleOptions.map((option, index) => {
                                const optionValue = String(option.value);
                                const isSelected =
                                    optionValue === selectedValue;
                                const isActive = index === activeIndex;

                                return (
                                    <li
                                        key={optionValue}
                                        role="option"
                                        aria-selected={isSelected}
                                    >
                                        <button
                                            type="button"
                                            className={cn(
                                                'flex w-full cursor-pointer items-center justify-start gap-2 px-4 py-2 text-start text-sm text-gray-700',
                                                'hover:bg-gray-100 focus:bg-gray-100 focus:outline-none',
                                                'dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:bg-gray-600 dark:focus:text-white',
                                                (isSelected || isActive) &&
                                                    'bg-gray-100 dark:bg-gray-600',
                                                isSelected && 'font-medium',
                                            )}
                                            onMouseEnter={() =>
                                                setActiveIndex(index)
                                            }
                                            onClick={() => selectOption(option)}
                                        >
                                            {isSelected ? (
                                                <Check className="size-4 shrink-0 text-gray-500 dark:text-gray-400" />
                                            ) : (
                                                <span className="size-4 shrink-0" />
                                            )}
                                            <span className="min-w-0 flex-1 truncate">
                                                {option.label}
                                            </span>
                                        </button>
                                    </li>
                                );
                            })
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
}
