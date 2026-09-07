import { createTheme } from 'flowbite-react';

/**
 * RTL overrides for Flowbite defaults that hardcode physical left/right.
 * Prefer logical start/end so padding and icon placement follow `dir`.
 * Select chevron background-position stays physical (`left`) — no reliable logical equivalent.
 */
export const flowbiteTheme = createTheme({
    alert: {
        closeButton: {
            base: '-m-1.5 ms-auto inline-flex h-8 w-8 cursor-pointer rounded-lg p-1.5 focus:ring-2',
        },
        icon: 'me-3 inline h-5 w-5 shrink-0',
    },
    breadcrumb: {
        item: {
            chevron:
                'mx-1 h-4 w-4 rotate-180 text-gray-400 group-first:hidden md:mx-2',
            icon: 'me-2 h-4 w-4',
        },
    },
    button: {
        base: 'relative flex cursor-pointer items-center justify-center rounded-lg text-center font-medium focus:outline-none focus:ring-4',
        disabled: 'pointer-events-none cursor-not-allowed opacity-50',
    },
    dropdown: {
        arrowIcon: 'ms-2 h-4 w-4',
        inlineWrapper: 'flex w-full cursor-pointer items-center',
        floating: {
            base: 'z-10 w-fit min-w-56 divide-y divide-gray-100 rounded-lg shadow focus:outline-none',
            header: 'block px-4 py-3 text-sm text-gray-700 dark:text-gray-200',
            item: {
                base: 'flex w-full cursor-pointer items-center justify-start gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:bg-gray-600 dark:focus:text-white',
                icon: 'size-4 shrink-0 text-gray-500 dark:text-gray-400',
            },
        },
    },
    avatar: {
        root: {
            base: 'flex shrink-0 items-center justify-center rounded',
        },
    },
    select: {
        addon:
            'inline-flex items-center rounded-s-md border border-e-0 border-gray-300 bg-gray-200 px-3 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-400',
        field: {
            icon: {
                base: 'pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3',
            },
            select: {
                base: 'block w-full cursor-pointer appearance-none border bg-arrow-down-icon bg-[length:0.75em_0.75em] bg-[position:left_12px_center] bg-no-repeat pe-10 focus:outline-none focus:ring-1 disabled:cursor-not-allowed disabled:opacity-50',
                withIcon: {
                    on: 'ps-10',
                    off: '',
                },
                withAddon: {
                    on: 'rounded-e-lg',
                    off: 'rounded-lg',
                },
            },
        },
    },
    sidebar: {
        root: {
            base: 'h-full',
            collapsed: {
                on: 'w-16',
                off: 'w-64',
            },
            inner: 'flex h-full w-full flex-col overflow-hidden bg-white px-4 py-4 dark:bg-gray-800',
        },
        collapse: {
            icon: {
                base: 'h-5 w-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white',
            },
            label: {
                base: 'ms-3 flex-1 whitespace-nowrap text-start',
            },
        },
        item: {
            base: 'flex w-full cursor-pointer items-center justify-start rounded-lg px-2 py-1.5 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700',
            collapsed: {
                insideCollapse: 'group w-full ps-8 transition duration-75',
            },
            content: {
                base: 'flex-1 truncate whitespace-nowrap ps-2.5 text-start',
            },
            icon: {
                base: 'h-5 w-5 shrink-0 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white',
                active: 'text-gray-700 dark:text-gray-100',
            },
        },
        itemGroup: {
            base: 'mt-3 space-y-0.5 border-t border-gray-200 pt-3 first:mt-0 first:border-t-0 first:pt-0 dark:border-gray-700',
        },
        logo: {
            base: 'mb-5 flex items-center ps-2.5',
            img: 'me-3 h-6 sm:h-7',
        },
    },
    textInput: {
        addon:
            'inline-flex items-center rounded-s-md border border-e-0 border-gray-300 bg-gray-200 px-3 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-400',
        field: {
            icon: {
                base: 'pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3',
            },
            rightIcon: {
                base: 'pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3',
            },
            input: {
                withRightIcon: {
                    on: 'pe-10',
                    off: '',
                },
                withIcon: {
                    on: 'ps-10',
                    off: '',
                },
                withAddon: {
                    on: 'rounded-e-lg',
                    off: 'rounded-lg',
                },
            },
        },
    },
    toast: {
        toggle: {
            base: '-m-1.5 ms-auto inline-flex h-8 w-8 cursor-pointer rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 dark:bg-gray-800 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-white',
        },
    },
});
