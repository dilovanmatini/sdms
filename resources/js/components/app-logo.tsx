import { usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <>
            <AppLogoIcon className="size-8" />
            <div className="ms-2 grid text-start text-sm">
                <span className="truncate font-semibold text-gray-900 dark:text-white">
                    {name}
                </span>
            </div>
        </>
    );
}
