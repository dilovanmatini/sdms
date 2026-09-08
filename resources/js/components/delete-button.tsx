import { router } from '@inertiajs/react';
import {
    Button,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Tooltip,
} from 'flowbite-react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';

type Props = {
    href: string;
    disabled?: boolean;
    disabledReason?: string;
    confirmTitle?: string;
    confirmMessage?: string;
};

export function DeleteButton({
    href,
    disabled = false,
    disabledReason = 'لا يمكن الحذف لوجود سجلات مرتبطة',
    confirmTitle = 'تأكيد الحذف',
    confirmMessage = 'هل أنت متأكد من الحذف؟ لا يمكن التراجع عن هذا الإجراء.',
}: Props) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const handleConfirm = () => {
        setProcessing(true);

        router.delete(href, {
            onFinish: () => {
                setProcessing(false);
                setOpen(false);
            },
        });
    };

    const button = (
        <Button
            color="light"
            size="xs"
            disabled={disabled}
            onClick={() => setOpen(true)}
            title={disabled ? undefined : 'حذف'}
            aria-label={disabled ? disabledReason : 'حذف'}
            className="inline-flex items-center justify-center p-2"
        >
            <Trash2 className="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
        </Button>
    );

    return (
        <>
            {disabled ? (
                <Tooltip content={disabledReason} placement="top">
                    <span className="inline-flex cursor-not-allowed">
                        {button}
                    </span>
                </Tooltip>
            ) : (
                button
            )}

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
                        إلغاء
                    </Button>
                    <Button
                        color="red"
                        onClick={handleConfirm}
                        disabled={processing}
                    >
                        {processing ? 'جارٍ الحذف...' : 'حذف'}
                    </Button>
                </ModalFooter>
            </Modal>
        </>
    );
}
