import { usePasskeyRegister } from '@laravel/passkeys/react';
import { Button, Label, TextInput } from 'flowbite-react';
import { useState } from 'react';
import InputError from '@/components/input-error';

type Props = {
    onSuccess: () => void;
};

export default function PasskeyRegistration({ onSuccess }: Props) {
    const [name, setName] = useState(() => {
        const ua = navigator.userAgent;

        const browser = [
            { pattern: /Edg|Edge/, name: 'Edge' },
            { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
            { pattern: /Firefox|FxiOS/, name: 'Firefox' },
            { pattern: /Chrome|CriOS/, name: 'Chrome' },
            { pattern: /Safari/, name: 'Safari' },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        const os = [
            { pattern: /iPhone/, name: 'iPhone' },
            { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
            { pattern: /Android/, name: 'Android' },
            { pattern: /Mac/, name: 'Mac' },
            { pattern: /Windows/, name: 'Windows' },
        ].find(({ pattern }) => pattern.test(ua))?.name;

        return [browser, os].filter(Boolean).join(' على ') || '';
    });

    const [showForm, setShowForm] = useState(false);
    const { register, isLoading, error, isSupported } = usePasskeyRegister({
        onSuccess: () => {
            setName('');
            setShowForm(false);
            onSuccess();
        },
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!name.trim()) {
            return;
        }

        await register(name);
    };

    const handleCancel = () => {
        setShowForm(false);
        setName('');
    };

    if (!isSupported) {
        return (
            <div className="text-sm text-gray-500 dark:text-gray-400">
                مفاتيح المرور غير مدعومة في هذا المتصفح.
            </div>
        );
    }

    if (!showForm) {
        return (
            <Button color="light" onClick={() => setShowForm(true)}>
                إضافة مفتاح مرور
            </Button>
        );
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800"
        >
            <div className="grid gap-2">
                <Label htmlFor="passkey-name">اسم مفتاح المرور</Label>
                <TextInput
                    id="passkey-name"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="مثال: MacBook Pro، iPhone"
                    className="mt-1 block w-full"
                    autoFocus
                />
                <p className="text-xs text-gray-500 dark:text-gray-400">
                    يساعد الاسم في التعرف على مفتاح المرور لاحقاً.
                </p>
            </div>

            {error && <InputError message={error} />}

            <div className="flex gap-2">
                <Button type="submit" disabled={isLoading || !name.trim()}>
                    {isLoading ? 'جارٍ التسجيل...' : 'تسجيل مفتاح المرور'}
                </Button>
                <Button type="button" color="gray" onClick={handleCancel}>
                    إلغاء
                </Button>
            </div>
        </form>
    );
}
