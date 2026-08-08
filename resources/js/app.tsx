import { createInertiaApp } from '@inertiajs/react';
import { ThemeProvider } from 'flowbite-react';
import { FlashToaster } from '@/components/flash-toaster';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SystemSettingsLayout from '@/layouts/settings/system-layout';
import UserSettingsLayout from '@/layouts/settings/user-layout';
import { flowbiteTheme } from '@/theme/flowbite';

const userSettingsPages = new Set([
    'settings/profile',
    'settings/security',
    'settings/appearance',
]);

createInertiaApp({
    title: (title, page) => {
        const name =
            typeof page.props.name === 'string' && page.props.name !== ''
                ? page.props.name
                : 'SDMS';

        return title ? `${title} - ${name}` : name;
    },
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name === 'settings/index':
                return AppLayout;
            case userSettingsPages.has(name):
                return [AppLayout, UserSettingsLayout];
            case name.startsWith('settings/'):
                return [AppLayout, SystemSettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <ThemeProvider
                theme={flowbiteTheme}
                applyTheme={{
                    alert: {
                        closeButton: { base: 'replace' },
                        icon: 'replace',
                    },
                    breadcrumb: {
                        item: {
                            chevron: 'replace',
                            icon: 'replace',
                        },
                    },
                    dropdown: {
                        arrowIcon: 'replace',
                        inlineWrapper: 'replace',
                        floating: {
                            base: 'replace',
                            header: 'replace',
                            item: {
                                base: 'replace',
                                icon: 'replace',
                            },
                        },
                    },
                    avatar: {
                        root: {
                            base: 'replace',
                        },
                    },
                    select: {
                        addon: 'replace',
                        field: {
                            icon: { base: 'replace' },
                            select: {
                                base: 'replace',
                                withIcon: 'replace',
                                withAddon: 'replace',
                            },
                        },
                    },
                    sidebar: {
                        root: {
                            base: 'replace',
                            collapsed: 'replace',
                            inner: 'replace',
                        },
                        collapse: {
                            icon: { base: 'replace' },
                            label: { base: 'replace' },
                        },
                        item: {
                            base: 'replace',
                            collapsed: { insideCollapse: 'replace' },
                            content: { base: 'replace' },
                            icon: {
                                base: 'replace',
                                active: 'replace',
                            },
                        },
                        itemGroup: {
                            base: 'replace',
                        },
                        logo: {
                            base: 'replace',
                            img: 'replace',
                        },
                    },
                    textInput: {
                        addon: 'replace',
                        field: {
                            icon: { base: 'replace' },
                            rightIcon: { base: 'replace' },
                            input: {
                                withRightIcon: 'replace',
                                withIcon: 'replace',
                                withAddon: 'replace',
                            },
                        },
                    },
                    toast: {
                        toggle: { base: 'replace' },
                    },
                }}
            >
                {app}
                <FlashToaster />
            </ThemeProvider>
        );
    },
    progress: {
        color: '#2563eb',
    },
});

// This will set light / dark mode on load...
initializeTheme();
