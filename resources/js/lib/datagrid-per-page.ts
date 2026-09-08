export const DATAGRID_PER_PAGE_OPTIONS = [5, 10, 20, 30, 50, 100] as const;

export type DatagridPerPageOption = (typeof DATAGRID_PER_PAGE_OPTIONS)[number];

export const DATAGRID_PER_PAGE_DEFAULT: DatagridPerPageOption = 10;

export const DATAGRID_PER_PAGE_STORAGE_KEY = 'datagrid_per_page';

export const DATAGRID_PER_PAGE_COOKIE = 'datagrid_per_page';

const isAllowedPerPage = (value: number): value is DatagridPerPageOption =>
    (DATAGRID_PER_PAGE_OPTIONS as readonly number[]).includes(value);

const readPreferences = (): Record<string, number> => {
    if (typeof window === 'undefined') {
        return {};
    }

    try {
        const raw =
            localStorage.getItem(DATAGRID_PER_PAGE_STORAGE_KEY) ??
            readCookie(DATAGRID_PER_PAGE_COOKIE);

        if (!raw) {
            return {};
        }

        const decoded = JSON.parse(raw) as unknown;

        if (!decoded || typeof decoded !== 'object' || Array.isArray(decoded)) {
            return {};
        }

        return Object.fromEntries(
            Object.entries(decoded).map(([key, value]) => [
                key,
                Number(value),
            ]),
        );
    } catch {
        return {};
    }
};

const readCookie = (name: string): string | null => {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(
        new RegExp(`(?:^|; )${name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}=([^;]*)`),
    );

    return match ? decodeURIComponent(match[1]) : null;
};

const writeCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${encodeURIComponent(value)};path=/;max-age=${maxAge};SameSite=Lax`;
};

export function getStoredDatagridPerPage(grid: string): DatagridPerPageOption | null {
    const value = readPreferences()[grid];

    if (typeof value !== 'number' || !isAllowedPerPage(value)) {
        return null;
    }

    return value;
}

export function setDatagridPerPage(
    grid: string,
    perPage: DatagridPerPageOption,
): void {
    if (typeof window === 'undefined') {
        return;
    }

    const preferences = readPreferences();
    preferences[grid] = perPage;

    const encoded = JSON.stringify(preferences);
    localStorage.setItem(DATAGRID_PER_PAGE_STORAGE_KEY, encoded);
    writeCookie(DATAGRID_PER_PAGE_COOKIE, encoded);
}
