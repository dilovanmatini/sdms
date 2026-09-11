import { Link, router, usePage } from '@inertiajs/react';
import {
    Button,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
} from 'flowbite-react';
import {
    CircleDollarSign,
    EllipsisVertical,
    FileText,
    Pencil,
    ScrollText,
    Trash2,
    Wallet,
} from 'lucide-react';
import {
    useEffect,
    useId,
    useLayoutEffect,
    useMemo,
    useRef,
    useState,
} from 'react';
import { createPortal } from 'react-dom';
import { cn } from '@/lib/utils';
import { createEdit as distributorsCreateEdit } from '@/routes/distributors';
import { createEdit as openingBalancesCreateEdit } from '@/routes/opening-balances';
import { createEdit as paymentReceiptsCreateEdit } from '@/routes/payment-receipts';
import {
    createEdit as salesInvoicesCreateEdit,
    index as salesInvoicesIndex,
} from '@/routes/sales-invoices';
import { index as statementsIndex } from '@/routes/statements';

type MenuPosition = {
    top: number;
    left: number;
};

type Shortcut = {
    key: string;
    label: string;
    href: string;
    icon: typeof FileText;
    ability: string;
};

type Props = {
    distributorId: number;
    distributorLabel: string;
    abilities?: string[];
    onNavigate?: () => void;
    deleteHref?: string;
    canDelete?: boolean;
    deleteDisabledReason?: string;
    buttonSize?: 'xs' | 'sm';
};

const EMPTY_ABILITIES: string[] = [];

export function distributorShortcuts(distributorId: number): Shortcut[] {
    return [
        {
            key: 'sales-invoice',
            label: 'فاتورة مبيعات جديدة',
            href: salesInvoicesCreateEdit.url(
                {},
                { query: { distributor_id: distributorId } },
            ),
            icon: FileText,
            ability: 'manage_sales',
        },
        {
            key: 'payment-receipt',
            label: 'سند قبض جديد',
            href: paymentReceiptsCreateEdit.url(
                {},
                { query: { distributor_id: distributorId } },
            ),
            icon: Wallet,
            ability: 'manage_receipts',
        },
        {
            key: 'opening-balance',
            label: 'مبلغ غير مسدد جديد',
            href: openingBalancesCreateEdit.url(
                {},
                { query: { distributor_id: distributorId } },
            ),
            icon: CircleDollarSign,
            ability: 'manage_opening_balances',
        },
        {
            key: 'statement',
            label: 'كشف حساب الموزع',
            href: statementsIndex.url({
                query: { distributor_id: distributorId },
            }),
            icon: ScrollText,
            ability: 'view_statements',
        },
        {
            key: 'sales-list',
            label: 'فواتير المبيعات',
            href: salesInvoicesIndex.url({
                query: { distributor_id: distributorId },
            }),
            icon: FileText,
            ability: 'manage_sales',
        },
        {
            key: 'edit',
            label: 'تعديل الموزع',
            href: distributorsCreateEdit.url(distributorId),
            icon: Pencil,
            ability: 'manage_distributors',
        },
    ];
}

export function DistributorActionsMenu({
    distributorId,
    distributorLabel,
    abilities: abilitiesProp,
    onNavigate,
    deleteHref,
    canDelete = true,
    deleteDisabledReason = 'لا يمكن الحذف لوجود سجلات مرتبطة',
    buttonSize = 'sm',
}: Props) {
    const { auth } = usePage().props;
    const abilities = abilitiesProp ?? auth.user?.abilities ?? EMPTY_ABILITIES;
    const [open, setOpen] = useState(false);
    const [position, setPosition] = useState<MenuPosition | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const buttonRef = useRef<HTMLButtonElement>(null);
    const menuRef = useRef<HTMLDivElement>(null);
    const menuId = useId();

    const shortcuts = useMemo(
        () =>
            distributorShortcuts(distributorId).filter((item) =>
                abilities.includes(item.ability),
            ),
        [abilities, distributorId],
    );

    const showDelete =
        Boolean(deleteHref) && abilities.includes('manage_distributors');

    useLayoutEffect(() => {
        if (!open || !buttonRef.current) {
            return;
        }

        const updatePosition = () => {
            const button = buttonRef.current;
            const menu = menuRef.current;

            if (!button) {
                return;
            }

            const rect = button.getBoundingClientRect();
            const menuWidth = menu?.offsetWidth ?? 224;
            const itemCount = shortcuts.length + (showDelete ? 1 : 0);
            const menuHeight = menu?.offsetHeight ?? itemCount * 40;
            const gap = 4;
            const viewportPadding = 8;

            let top = rect.bottom + gap;
            let left = rect.right - menuWidth;

            if (top + menuHeight > window.innerHeight - viewportPadding) {
                top = Math.max(viewportPadding, rect.top - menuHeight - gap);
            }

            left = Math.min(
                Math.max(viewportPadding, left),
                window.innerWidth - menuWidth - viewportPadding,
            );

            setPosition({ top, left });
        };

        updatePosition();

        window.addEventListener('resize', updatePosition);
        window.addEventListener('scroll', updatePosition, true);

        return () => {
            window.removeEventListener('resize', updatePosition);
            window.removeEventListener('scroll', updatePosition, true);
        };
    }, [open, shortcuts.length, showDelete]);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onPointerDown = (event: PointerEvent) => {
            const target = event.target as Node;

            if (
                buttonRef.current?.contains(target) ||
                menuRef.current?.contains(target)
            ) {
                return;
            }

            setOpen(false);
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

    if (shortcuts.length === 0 && !showDelete) {
        return null;
    }

    const handleDeleteConfirm = () => {
        if (!deleteHref) {
            return;
        }

        setDeleting(true);

        router.delete(deleteHref, {
            onFinish: () => {
                setDeleting(false);
                setDeleteOpen(false);
            },
        });
    };

    return (
        <>
            <Button
                ref={buttonRef}
                type="button"
                color="light"
                size={buttonSize}
                aria-expanded={open}
                aria-haspopup="menu"
                aria-controls={menuId}
                aria-label={`إجراءات ${distributorLabel}`}
                onClick={() => setOpen((current) => !current)}
                className={cn(
                    'inline-flex shrink-0 items-center justify-center',
                    buttonSize === 'xs' ? 'p-2' : 'p-2!',
                )}
            >
                <EllipsisVertical
                    className={buttonSize === 'xs' ? 'size-3.5' : 'size-4'}
                />
            </Button>

            {open &&
                createPortal(
                    <div
                        ref={menuRef}
                        id={menuId}
                        role="menu"
                        style={
                            position
                                ? {
                                      position: 'fixed',
                                      top: position.top,
                                      left: position.left,
                                  }
                                : {
                                      position: 'fixed',
                                      top: -9999,
                                      left: -9999,
                                      visibility: 'hidden',
                                  }
                        }
                        className="z-60 min-w-56 rounded-lg border border-gray-200 bg-white py-1 shadow dark:border-gray-600 dark:bg-gray-700"
                    >
                        {shortcuts.map((shortcut) => (
                            <Link
                                key={shortcut.key}
                                href={shortcut.href}
                                role="menuitem"
                                onClick={() => {
                                    setOpen(false);
                                    onNavigate?.();
                                }}
                                className="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                <shortcut.icon className="size-4 shrink-0 text-gray-500 dark:text-gray-400" />
                                {shortcut.label}
                            </Link>
                        ))}

                        {showDelete && (
                            <button
                                type="button"
                                role="menuitem"
                                disabled={!canDelete}
                                title={
                                    canDelete ? undefined : deleteDisabledReason
                                }
                                aria-label={
                                    canDelete
                                        ? 'حذف الموزع'
                                        : deleteDisabledReason
                                }
                                onClick={() => {
                                    if (!canDelete) {
                                        return;
                                    }

                                    setOpen(false);
                                    setDeleteOpen(true);
                                }}
                                className={cn(
                                    'flex w-full items-center gap-2 px-4 py-2 text-sm',
                                    canDelete
                                        ? 'text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600'
                                        : 'cursor-not-allowed text-gray-400 dark:text-gray-500',
                                )}
                            >
                                <Trash2 className="size-4 shrink-0" />
                                حذف الموزع
                            </button>
                        )}
                    </div>,
                    document.body,
                )}

            {showDelete && (
                <Modal
                    show={deleteOpen}
                    onClose={() => {
                        if (!deleting) {
                            setDeleteOpen(false);
                        }
                    }}
                    size="md"
                    dismissible={!deleting}
                >
                    <ModalHeader>تأكيد الحذف</ModalHeader>
                    <ModalBody>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            هل أنت متأكد من حذف {distributorLabel}؟ لا يمكن
                            التراجع عن هذا الإجراء.
                        </p>
                    </ModalBody>
                    <ModalFooter className="justify-end gap-2">
                        <Button
                            color="light"
                            onClick={() => setDeleteOpen(false)}
                            disabled={deleting}
                        >
                            إلغاء
                        </Button>
                        <Button
                            color="red"
                            onClick={handleDeleteConfirm}
                            disabled={deleting}
                        >
                            {deleting ? 'جارٍ الحذف...' : 'حذف'}
                        </Button>
                    </ModalFooter>
                </Modal>
            )}
        </>
    );
}
