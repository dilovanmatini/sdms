import { router } from '@inertiajs/react';
import { Button } from 'flowbite-react';
import { Trash2 } from 'lucide-react';

type Props = {
    href: string;
    disabled?: boolean;
    confirmMessage?: string;
};

export function DeleteButton({
    href,
    disabled = false,
    confirmMessage = 'هل أنت متأكد من الحذف؟',
}: Props) {
    const handleDelete = () => {
        if (disabled) {
            return;
        }

        if (!window.confirm(confirmMessage)) {
            return;
        }

        router.delete(href);
    };

    return (
        <Button
            color="failure"
            size="xs"
            disabled={disabled}
            onClick={handleDelete}
            title={disabled ? 'لا يمكن الحذف لوجود سجلات مرتبطة' : 'حذف'}
        >
            <Trash2 className="h-3.5 w-3.5" />
        </Button>
    );
}
