import type { UrlMethodPair } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import { usePasskeyVerify } from '@laravel/passkeys/react';
import { Button, HR, Spinner } from 'flowbite-react';
import { KeyRound } from 'lucide-react';
import InputError from '@/components/input-error';

type Props = {
    routes?: {
        options: UrlMethodPair;
        submit: UrlMethodPair;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
};

export default function PasskeyVerify({
    routes,
    label,
    loadingLabel,
    separator,
}: Props = {}) {
    const { verify, isLoading, error, isSupported } = usePasskeyVerify({
        ...(routes && {
            routes: {
                options: routes.options.url,
                submit: routes.submit.url,
            },
        }),
        onSuccess: (response) => {
            router.visit(response.redirect ?? '/dashboard');
        },
    });

    if (!isSupported) {
        return null;
    }

    return (
        <>
            <div className="grid gap-2">
                <Button
                    type="button"
                    color="light"
                    className="w-full gap-2"
                    onClick={verify}
                    disabled={isLoading}
                >
                    {isLoading ? (
                        <Spinner size="sm" />
                    ) : (
                        <KeyRound className="h-4 w-4" />
                    )}
                    {isLoading
                        ? (loadingLabel ?? 'جاري التحقق...')
                        : (label ?? 'تسجيل الدخول بمفتاح المرور')}
                </Button>
                {error && (
                    <InputError message={error} className="text-center" />
                )}
            </div>

            <div className="relative my-6">
                <HR />
                <div className="absolute inset-0 flex items-center justify-center">
                    <span className="bg-gray-50 px-2 text-xs text-gray-500 uppercase dark:bg-gray-900 dark:text-gray-400">
                        {separator ?? 'أو المتابعة بكلمة المرور'}
                    </span>
                </div>
            </div>
        </>
    );
}
