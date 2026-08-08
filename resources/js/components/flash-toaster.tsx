import { router } from '@inertiajs/react';
import { Toast, ToastToggle } from 'flowbite-react';
import { AlertCircle, CheckCircle2, Info, TriangleAlert } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { FlashToast } from '@/types/ui';

type ToastItem = FlashToast & { id: number };

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
            setToasts((current) => [...current, { ...data, id }]);

            window.setTimeout(() => {
                setToasts((current) =>
                    current.filter((toast) => toast.id !== id),
                );
            }, 4000);
        });
    }, []);

    if (toasts.length === 0) {
        return null;
    }

    return (
        <div className="fixed top-4 end-4 z-50 flex flex-col gap-3">
            {toasts.map((toast) => {
                const Icon = icons[toast.type];

                return (
                    <Toast key={toast.id} className="gap-3">
                        <div
                            className={`inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${iconStyles[toast.type]}`}
                        >
                            <Icon className="h-5 w-5" />
                        </div>
                        <div className="text-sm font-normal">
                            {toast.message}
                        </div>
                        <ToastToggle
                            onDismiss={() =>
                                setToasts((current) =>
                                    current.filter(
                                        (item) => item.id !== toast.id,
                                    ),
                                )
                            }
                        />
                    </Toast>
                );
            })}
        </div>
    );
}
