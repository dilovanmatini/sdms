import { useMemo } from 'react';
import {
    SearchableSelect
    
} from '@/components/searchable-select';
import type {SearchableSelectOption} from '@/components/searchable-select';
import { useLookupOptions } from '@/hooks/use-lookup-options';

type Props = {
    id?: string;
    name: string;
    buildUrl: (search: string) => string;
    initialOptions?: SearchableSelectOption[];
    defaultValue?: string | number | null;
    value?: string | number | null;
    onChange?: (value: string, option?: SearchableSelectOption | null) => void;
    placeholder?: string;
    searchPlaceholder?: string;
    required?: boolean;
    disabled?: boolean;
    className?: string;
    emptyMessage?: string;
};

/**
 * SearchableSelect wired to a JSON lookup endpoint (AJAX).
 * Prefer this for catalogs expected to grow past a handful of rows.
 */
export function AsyncSearchableSelect({
    buildUrl,
    initialOptions = [],
    onChange,
    ...props
}: Props) {
    const initialOptionsKey = JSON.stringify(initialOptions);
    const stableInitialOptions = useMemo(
        () => initialOptions,
        // initialOptionsKey captures content changes from server/selected rows.
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [initialOptionsKey],
    );

    const { options, loading, onSearch } = useLookupOptions({
        buildUrl,
        initialOptions: stableInitialOptions,
    });

    return (
        <SearchableSelect
            {...props}
            options={options}
            loading={loading}
            onSearch={onSearch}
            onChange={onChange}
        />
    );
}

export type { SearchableSelectOption };
