import { useHttp } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { SearchableSelectOption } from '@/components/searchable-select';

type LookupResponse = {
    data: SearchableSelectOption[];
};

type QueryBag = Record<
    string,
    string | number | boolean | undefined | Array<string | number>
>;

type UseLookupOptionsArgs = {
    /** Builds the lookup URL for the current search term. */
    buildUrl: (search: string) => string;
    initialOptions?: SearchableSelectOption[];
};

/**
 * Selected/pinned options first, then remote results, never duplicated.
 */
function mergeOptions(
    remote: SearchableSelectOption[],
    pinned: SearchableSelectOption[],
): SearchableSelectOption[] {
    const pinnedIds = new Set(pinned.map((option) => String(option.value)));

    return [
        ...pinned,
        ...remote.filter((option) => !pinnedIds.has(String(option.value))),
    ];
}

export function useLookupOptions({
    buildUrl,
    initialOptions = [],
}: UseLookupOptionsArgs) {
    const [remoteOptions, setRemoteOptions] = useState<
        SearchableSelectOption[]
    >([]);
    const buildUrlRef = useRef(buildUrl);
    const { get, processing } = useHttp({});

    useEffect(() => {
        buildUrlRef.current = buildUrl;
    }, [buildUrl]);

    const options = useMemo(
        () => mergeOptions(remoteOptions, initialOptions),
        [remoteOptions, initialOptions],
    );

    const onSearch = useCallback(
        (query: string) => {
            get(buildUrlRef.current(query), {
                onSuccess: (response) => {
                    const payload = response as LookupResponse;
                    setRemoteOptions(payload.data ?? []);
                },
            });
        },
        [get],
    );

    return {
        options,
        loading: processing,
        onSearch,
    };
}

export function lookupQuery(
    search: string,
    extra: QueryBag = {},
): { query: QueryBag } {
    return {
        query: {
            search: search || undefined,
            ...extra,
        },
    };
}
