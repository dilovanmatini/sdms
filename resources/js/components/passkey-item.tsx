import {
    Badge,
    Button,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
} from 'flowbite-react';
import { KeyRound, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { Passkey } from '@/types/auth';

type Props = {
    passkey: Passkey;
    onDelete: (id: number, onError: () => void) => void;
};

export default function PasskeyItem({ passkey, onDelete }: Props) {
    const [isDeleting, setIsDeleting] = useState(false);
    const [open, setOpen] = useState(false);

    const handleDelete = () => {
        setIsDeleting(true);
        onDelete(passkey.id, () => setIsDeleting(false));
    };

    return (
        <div className="flex items-center justify-between border-b border-gray-200 p-4 last:border-b-0 dark:border-gray-700">
            <div className="flex items-center gap-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
                    <KeyRound className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                </div>
                <div className="space-y-1">
                    <div className="flex items-center gap-2.5">
                        <p className="font-medium tracking-tight text-gray-900 dark:text-white">
                            {passkey.name}
                        </p>
                        {passkey.authenticator && (
                            <Badge color="gray">{passkey.authenticator}</Badge>
                        )}
                    </div>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        أُضيف {passkey.created_at_diff}
                        {passkey.last_used_at_diff && (
                            <>
                                <span className="mx-1 text-gray-400">/</span>
                                آخر استخدام {passkey.last_used_at_diff}
                            </>
                        )}
                    </p>
                </div>
            </div>

            <Button color="red" size="sm" outline onClick={() => setOpen(true)}>
                <Trash2 className="h-4 w-4" />
                <span className="sr-only">إزالة</span>
            </Button>

            <Modal show={open} onClose={() => setOpen(false)} dismissible>
                <ModalHeader>إزالة مفتاح المرور</ModalHeader>
                <ModalBody>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        هل أنت متأكد أنك تريد إزالة مفتاح المرور «{passkey.name}
                        »؟ لن تتمكن من استخدامه لتسجيل الدخول بعد ذلك.
                    </p>
                </ModalBody>
                <ModalFooter className="justify-end">
                    <Button color="light" onClick={() => setOpen(false)}>
                        إلغاء
                    </Button>
                    <Button
                        color="red"
                        onClick={handleDelete}
                        disabled={isDeleting}
                    >
                        {isDeleting ? 'جارٍ الإزالة...' : 'إزالة مفتاح المرور'}
                    </Button>
                </ModalFooter>
            </Modal>
        </div>
    );
}
