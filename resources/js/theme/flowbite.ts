import { createTheme } from 'flowbite-react';

/**
 * Soft focus: kill Flowbite's thick rings (defaults merge via twMerge) and use
 * a light black glow (`shadow-focus` from app.css). Buttons use focus-visible;
 * fields use focus so mouse users still see the active field.
 */
const controlFocus =
    'focus:outline-none focus:ring-0 focus-visible:shadow-focus';
const fieldFocus =
    'focus:outline-none focus:ring-0 focus:border-gray-300 focus:shadow-focus dark:focus:border-gray-600';
/** Neutralize Flowbite gray field colors that force focus:border-primary-500 */
const fieldGray =
    'border-gray-300 bg-gray-50 text-gray-900 focus:border-gray-300 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-gray-600 dark:focus:ring-0';

/**
 * RTL overrides for Flowbite defaults that hardcode physical left/right.
 * Prefer logical start/end so padding and icon placement follow `dir`.
 * Select chevron background-position stays physical (`left`) — no reliable logical equivalent.
 */
export const flowbiteTheme = createTheme({
    alert: {
        closeButton: {
            base: `-m-1.5 ms-auto inline-flex h-8 w-8 cursor-pointer rounded-lg p-1.5 ${controlFocus}`,
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
        base: `relative flex cursor-pointer items-center justify-center rounded-lg text-center font-medium ${controlFocus}`,
        disabled: 'pointer-events-none cursor-not-allowed opacity-50',
        grouped: 'focus:ring-0',
    },
    checkbox: {
        base: `h-4 w-4 appearance-none rounded border border-gray-300 bg-gray-100 bg-[length:0.55em_0.55em] bg-center bg-no-repeat checked:border-transparent checked:bg-current checked:bg-check-icon ${fieldFocus} focus:ring-offset-0 dark:border-gray-600 dark:bg-gray-700 dark:checked:border-transparent dark:checked:bg-current`,
    },
    dropdown: {
        arrowIcon: 'ms-2 h-4 w-4',
        inlineWrapper: 'flex w-full cursor-pointer items-center',
        floating: {
            base: 'z-10 w-fit min-w-56 rounded-lg shadow focus:outline-none',
            header: 'block px-4 py-3 text-sm text-gray-700 dark:text-gray-200',
            item: {
                base: 'flex w-full cursor-pointer items-center justify-start gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:bg-gray-600 dark:focus:text-white',
                icon: 'size-4 shrink-0 text-gray-500 dark:text-gray-400',
            },
        },
    },
    fileInput: {
        base: `block w-full cursor-pointer rounded-lg border file:-ms-4 file:me-4 file:cursor-pointer file:border-none file:bg-gray-800 file:py-2.5 file:pe-4 file:ps-8 file:text-sm file:font-medium file:leading-[inherit] file:text-white hover:file:bg-gray-700 ${fieldFocus} dark:file:bg-gray-600 dark:hover:file:bg-gray-500`,
        colors: {
            gray: fieldGray,
        },
    },
    modal: {
        header: {
            base: 'flex items-start justify-between rounded-t border-b border-gray-200 p-5 dark:border-gray-600',
        },
    },
    navbar: {
        toggle: {
            base: `inline-flex items-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 ${controlFocus} md:hidden dark:text-gray-400 dark:hover:bg-gray-700`,
        },
    },
    avatar: {
        root: {
            base: 'flex shrink-0 items-center justify-center rounded',
        },
    },
    radio: {
        base: `h-4 w-4 appearance-none rounded-full border border-gray-300 bg-gray-100 bg-[length:1em_1em] bg-center bg-no-repeat checked:border-transparent checked:bg-current checked:bg-dot-icon ${fieldFocus} focus:ring-offset-0 dark:border-gray-600 dark:bg-gray-700 dark:checked:border-transparent dark:checked:bg-current`,
    },
    select: {
        addon: 'inline-flex items-center rounded-s-md border border-e-0 border-gray-300 bg-gray-200 px-3 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-400',
        field: {
            icon: {
                base: 'pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3',
            },
            select: {
                base: `block w-full cursor-pointer appearance-none border bg-arrow-down-icon bg-[length:0.75em_0.75em] bg-[position:left_12px_center] bg-no-repeat pe-10 ${fieldFocus} disabled:cursor-not-allowed disabled:opacity-50`,
                colors: {
                    gray: fieldGray,
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
        addon: 'inline-flex items-center rounded-s-md border border-e-0 border-gray-300 bg-gray-200 px-3 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-400',
        field: {
            icon: {
                base: 'pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3',
            },
            rightIcon: {
                base: 'pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3',
            },
            input: {
                base: `block w-full border ${fieldFocus} disabled:cursor-not-allowed disabled:opacity-50`,
                colors: {
                    gray: fieldGray,
                },
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
    textarea: {
        base: `block w-full rounded-lg border p-2.5 text-sm ${fieldFocus} disabled:cursor-not-allowed disabled:opacity-50`,
        colors: {
            gray: fieldGray,
        },
    },
    toast: {
        toggle: {
            base: `-m-1.5 ms-auto inline-flex h-8 w-8 cursor-pointer rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 ${controlFocus} dark:bg-gray-800 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-white`,
        },
    },
    toggleSwitch: {
        toggle: {
            base: 'relative rounded-full after:absolute after:rounded-full after:border after:bg-white after:transition-all group-focus:ring-0 group-focus-visible:shadow-focus',
            checked: {
                color: {
                    default: 'bg-primary-700',
                },
            },
        },
    },
});
