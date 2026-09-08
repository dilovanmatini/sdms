import { router } from '@inertiajs/react';
import {
    Button,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
} from 'flowbite-react';
import type { ComponentProps, ReactNode } from 'react';
import { useState } from 'react';

type ButtonColor = NonNullable<ComponentProps<typeof Button>['color']>;
type ButtonSize = NonNullable<ComponentProps<typeof Button>['size']>;

type Props = {
    href: string;
    method?: 'post' | 'put' | 'patch' | 'delete';
    children: ReactNode;
    confirmTitle: string;
    confirmMessage: string;
    confirmLabel: string;
    confirmingLabel?: string;
    cancelLabel?: string;
    confirmColor?: ButtonColor;
    color?: ButtonColor;
    size?: ButtonSize;
    className?: string;
    disabled?: boolean;
    title?: string;
    'aria-label'?: string;
};

export function ConfirmActionButton({
    href,
    method = 'post',
    children,
    confirmTitle,
    confirmMessage,
    confirmLabel,
    confirmingLabel,
    cancelLabel = 'إلغاء',
    confirmColor = 'red',
    color = 'light',
    size,
    className,
    disabled = false,
    title,
    'aria-label': ariaLabel,
}: Props) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const handleConfirm = () => {
        setProcessing(true);

        router.visit(href, {
            method,
            onFinish: () => {
                setProcessing(false);
                setOpen(false);
            },
        });
    };

    return (
        <>
            <Button
                type="button"
                color={color}
                size={size}
                disabled={disabled}
                onClick={() => setOpen(true)}
                title={title}
                aria-label={ariaLabel}
                className={className}
            >
                {children}
            </Button>

            <Modal
                show={open}
                onClose={() => {
                    if (!processing) {
                        setOpen(false);
                    }
                }}
                size="md"
                dismissible={!processing}
            >
                <ModalHeader>{confirmTitle}</ModalHeader>
                <ModalBody>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        {confirmMessage}
                    </p>
                </ModalBody>
                <ModalFooter className="justify-end gap-2">
                    <Button
                        color="light"
                        onClick={() => setOpen(false)}
                        disabled={processing}
                    >
                        {cancelLabel}
                    </Button>
                    <Button
                        color={confirmColor}
                        onClick={handleConfirm}
                        disabled={processing}
                    >
                        {processing
                            ? (confirmingLabel ?? `${confirmLabel}...`)
                            : confirmLabel}
                    </Button>
                </ModalFooter>
            </Modal>
        </>
    );
}
