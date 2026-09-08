import { router } from '@inertiajs/react';
import { Toast, ToastToggle } from 'flowbite-react';
import { AlertCircle, CheckCircle2, Info, TriangleAlert } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FlashToast } from '@/types/ui';

type ToastItem = FlashToast & { id: number; open: boolean };

const TOAST_DURATION_MS = 4000;
const TOAST_TRANSITION_MS = 300;

const icons = {
    success: CheckCircle2,
    info: Info,
    warning: TriangleAlert,
    error: AlertCircle,
} as const;

const iconStyles = {
    success: 'bg-green-100 text-green-500 dark:bg-green-800 dark:text-green-200',
    info: 'bg-blue-100 text-blue-500 dark:bg-blue-800 dark:text-blue-200',
    warning:
        'bg-yellow-100 text-yellow-500 dark:bg-yellow-800 dark:text-yellow-200',
    error: 'bg-red-100 text-red-500 dark:bg-red-800 dark:text-red-200',
} as const;

export function FlashToaster() {
    const [toasts, setToasts] = useState<ToastItem[]>([]);

    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data = flash?.toast as FlashToast | undefined;

            if (!data) {
                return;
            }

            const id = Date.now();
            setToasts((current) => [...current, { ...data, id, open: false }]);

            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(() => {
                    setToasts((current) =>
                        current.map((toast) =>
                            toast.id === id ? { ...toast, open: true } : toast,
                        ),
                    );
                });
            });

            window.setTimeout(() => {
                dismissToast(id);
            }, TOAST_DURATION_MS);
        });
    }, []);

    function dismissToast(id: number): void {
        setToasts((current) =>
            current.map((toast) =>
                toast.id === id ? { ...toast, open: false } : toast,
            ),
        );

        window.setTimeout(() => {
            setToasts((current) =>
                current.filter((toast) => toast.id !== id),
            );
        }, TOAST_TRANSITION_MS);
    }

    if (toasts.length === 0) {
        return null;
    }

    return (
        <div className="fixed bottom-4 end-4 z-50 flex flex-col gap-3">
            {toasts.map((toast) => {
                const Icon = icons[toast.type];

                return (
                    <Toast
                        key={toast.id}
                        className={`gap-3 transition duration-300 ease-out ${
                            toast.open
                                ? 'translate-x-0 opacity-100'
                                : 'opacity-0 ltr:translate-x-full rtl:-translate-x-full'
                        }`}
                    >
                        <div
                            className={`inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${iconStyles[toast.type]}`}
                        >
                            <Icon className="h-5 w-5" />
                        </div>
                        <div className="text-sm font-normal">
                            {toast.message}
                        </div>
                        <ToastToggle onDismiss={() => dismissToast(toast.id)} />
                    </Toast>
                );
            })}
        </div>
    );
}
