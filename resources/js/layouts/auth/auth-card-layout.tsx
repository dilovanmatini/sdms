import { Link } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import type { PropsWithChildren } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';

export default function AuthCardLayout({
    children,
    title,
    description,
}: PropsWithChildren<{
    name?: string;
    title?: string;
    description?: string;
}>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-gray-100 p-6 md:p-10 dark:bg-gray-900">
            <div className="flex w-full max-w-md flex-col gap-6">
                <Link
                    href={home()}
                    className="flex items-center gap-2 self-center font-medium"
                >
                    <div className="flex h-9 w-9 items-center justify-center">
                        <AppLogoIcon className="size-12" />
                    </div>
                </Link>

                <Card className="rounded-xl">
                    <div className="space-y-1 text-center">
                        <h1 className="text-xl font-semibold text-gray-900 dark:text-white">
                            {title}
                        </h1>
                        {description && (
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {description}
                            </p>
                        )}
                    </div>
                    <div className="pt-4">{children}</div>
                </Card>
            </div>
        </div>
    );
}
