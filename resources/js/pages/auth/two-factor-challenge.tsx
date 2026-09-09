import { Form, Head, setLayoutProps } from '@inertiajs/react';
import { Button, TextInput } from 'flowbite-react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import { OTP_MAX_LENGTH } from '@/hooks/use-two-factor-auth';
import { store } from '@/routes/two-factor/login';

export default function TwoFactorChallenge() {
    const [showRecoveryInput, setShowRecoveryInput] = useState<boolean>(false);
    const [code, setCode] = useState<string>('');

    const authConfigContent = useMemo<{
        title: string;
        description: string;
        toggleText: string;
    }>(() => {
        if (showRecoveryInput) {
            return {
                title: 'رمز الاسترداد',
                description:
                    'يرجى تأكيد الوصول إلى حسابك بإدخال أحد رموز الاسترداد الطارئة.',
                toggleText: 'تسجيل الدخول باستخدام رمز المصادقة',
            };
        }

        return {
            title: 'رمز المصادقة',
            description: 'أدخل رمز المصادقة من تطبيق المصادقة لديك.',
            toggleText: 'تسجيل الدخول باستخدام رمز الاسترداد',
        };
    }, [showRecoveryInput]);

    setLayoutProps({
        title: authConfigContent.title,
        description: authConfigContent.description,
    });

    const toggleRecoveryMode = (clearErrors: () => void): void => {
        setShowRecoveryInput(!showRecoveryInput);
        clearErrors();
        setCode('');
    };

    return (
        <>
            <Head title="المصادقة الثنائية" />

            <div className="space-y-6">
                <Form
                    {...store.form()}
                    className="space-y-4"
                    resetOnError
                    resetOnSuccess={!showRecoveryInput}
                >
                    {({ errors, processing, clearErrors }) => (
                        <>
                            {showRecoveryInput ? (
                                <>
                                    <TextInput
                                        name="recovery_code"
                                        type="text"
                                        placeholder="أدخل رمز الاسترداد"
                                        autoFocus={showRecoveryInput}
                                        required
                                    />
                                    <InputError
                                        message={errors.recovery_code}
                                    />
                                </>
                            ) : (
                                <div className="flex flex-col items-center justify-center space-y-3 text-center">
                                    <TextInput
                                        name="code"
                                        type="text"
                                        inputMode="numeric"
                                        autoComplete="one-time-code"
                                        maxLength={OTP_MAX_LENGTH}
                                        value={code}
                                        onChange={(event) =>
                                            setCode(
                                                event.target.value.replace(
                                                    /\D/g,
                                                    '',
                                                ),
                                            )
                                        }
                                        disabled={processing}
                                        autoFocus
                                        required
                                        className="w-48 text-center text-lg tracking-[0.35em]"
                                        placeholder="000000"
                                    />
                                    <InputError message={errors.code} />
                                </div>
                            )}

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                متابعة
                            </Button>

                            <div className="text-center text-sm text-gray-500 dark:text-gray-400">
                                <span>أو يمكنك </span>
                                <button
                                    type="button"
                                    className="cursor-pointer text-gray-900 underline decoration-gray-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current dark:text-white dark:decoration-gray-500"
                                    onClick={() =>
                                        toggleRecoveryMode(clearErrors)
                                    }
                                >
                                    {authConfigContent.toggleText}
                                </button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
