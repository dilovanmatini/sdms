import { usePage } from '@inertiajs/react';

export type SharedCurrency = {
    code: string;
    symbol: string;
    label: string;
};

export function useCurrency(): SharedCurrency {
    return usePage().props.currency;
}

/**
 * Append a currency symbol to a numeric or preformatted amount.
 */
export function formatMoney(amount: string | number, symbol: string): string {
    const value =
        typeof amount === 'number' ? amount.toFixed(2) : String(amount);

    return `${value} ${symbol}`;
}

/**
 * Hook that returns a formatter bound to the shared system currency symbol.
 */
export function useFormatMoney(): (amount: string | number) => string {
    const { symbol } = useCurrency();

    return (amount) => formatMoney(amount, symbol);
}
