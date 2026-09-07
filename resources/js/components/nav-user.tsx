import { usePage } from '@inertiajs/react';
import { ChevronsUpDown } from 'lucide-react';
import { useEffect, useId, useRef, useState } from 'react';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { cn } from '@/lib/utils';

export function NavUser({ collapsed = false }: { collapsed?: boolean }) {
    const { auth } = usePage().props;
    const [open, setOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);
    const menuId = useId();

    useEffect(() => {
        if (!open) {
            return;
        }

        const onPointerDown = (event: PointerEvent) => {
            if (!containerRef.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        document.addEventListener('pointerdown', onPointerDown);
        document.addEventListener('keydown', onKeyDown);

        return () => {
            document.removeEventListener('pointerdown', onPointerDown);
            document.removeEventListener('keydown', onKeyDown);
        };
    }, [open]);

    if (!auth.user) {
        return null;
    }

    return (
        <div ref={containerRef} className="relative w-full">
            <button
                type="button"
                aria-expanded={open}
                aria-haspopup="menu"
                aria-controls={menuId}
                onClick={() => setOpen((current) => !current)}
                className={cn(
                    'flex w-full items-center rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700',
                    collapsed ? 'justify-center' : 'gap-2',
                    open && 'bg-gray-100 dark:bg-gray-700',
                )}
                data-test="sidebar-menu-button"
            >
                <UserInfo
                    user={auth.user}
                    showEmail={false}
                    showName={!collapsed}
                    className={collapsed ? undefined : 'min-w-0 flex-1'}
                />
                {!collapsed && (
                    <ChevronsUpDown className="ms-auto size-4 shrink-0 text-gray-500" />
                )}
            </button>

            {open && (
                <div
                    id={menuId}
                    role="menu"
                    className={cn(
                        'absolute z-50 min-w-56 divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white py-1 shadow focus:outline-none dark:border-gray-600 dark:bg-gray-700',
                        collapsed
                            ? 'end-full bottom-0 me-2'
                            : 'inset-s-0 bottom-full mb-2 w-full',
                    )}
                >
                    <UserMenuContent
                        user={auth.user}
                        onNavigate={() => setOpen(false)}
                    />
                </div>
            )}
        </div>
    );
}
