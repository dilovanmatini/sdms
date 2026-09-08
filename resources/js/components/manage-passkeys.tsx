import { router } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';
import { FormCard } from '@/components/form-card';
import PasskeyItem from '@/components/passkey-item';
import PasskeyRegistration from '@/components/passkey-register';
import type { Passkey } from '@/types/auth';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

const EmptyState = () => {
    return (
        <div className="p-8 text-center">
            <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                <KeyRound className="h-7 w-7 text-gray-500 dark:text-gray-400" />
            </div>
            <p className="font-medium text-gray-900 dark:text-white">
                لا توجد مفاتيح مرور بعد
            </p>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                أضف مفتاح مرور لتسجيل الدخول دون كلمة مرور
            </p>
        </div>
    );
};

export default function ManagePasskeys(props: Props) {
    const passkeys = props.passkeys ?? [];

    const handleDelete = (id: number, onError: () => void) => {
        router.delete(destroy.url(id), {
            preserveScroll: true,
            onError,
        });
    };

    const handleRegisterSuccess = () => {
        router.reload();
    };

    if (!(props.canManagePasskeys ?? false)) {
        return null;
    }

    return (
        <FormCard
            title="مفاتيح المرور"
            description="إدارة مفاتيح المرور لتسجيل الدخول دون كلمة مرور"
            icon={KeyRound}
        >
            <div className="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                {passkeys.length > 0 ? (
                    passkeys.map((passkey) => (
                        <PasskeyItem
                            key={passkey.id}
                            passkey={passkey}
                            onDelete={handleDelete}
                        />
                    ))
                ) : (
                    <EmptyState />
                )}
            </div>

            <PasskeyRegistration onSuccess={handleRegisterSuccess} />
        </FormCard>
    );
}
