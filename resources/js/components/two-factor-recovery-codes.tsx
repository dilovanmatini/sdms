import { Form } from '@inertiajs/react';
import { Button, Card } from 'flowbite-react';
import { Eye, EyeOff, LockKeyhole, RefreshCw } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import AlertError from '@/components/alert-error';
import { regenerateRecoveryCodes } from '@/routes/two-factor';

type Props = {
    recoveryCodesList: string[];
    fetchRecoveryCodes: () => Promise<void>;
    errors: string[];
};

export default function TwoFactorRecoveryCodes({
    recoveryCodesList,
    fetchRecoveryCodes,
    errors,
}: Props) {
    const [codesAreVisible, setCodesAreVisible] = useState<boolean>(false);
    const codesSectionRef = useRef<HTMLDivElement | null>(null);
    const canRegenerateCodes = recoveryCodesList.length > 0 && codesAreVisible;

    const toggleCodesVisibility = useCallback(async () => {
        if (!codesAreVisible && !recoveryCodesList.length) {
            await fetchRecoveryCodes();
        }

        setCodesAreVisible(!codesAreVisible);

        if (!codesAreVisible) {
            setTimeout(() => {
                codesSectionRef.current?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                });
            });
        }
    }, [codesAreVisible, recoveryCodesList.length, fetchRecoveryCodes]);

    useEffect(() => {
        if (!recoveryCodesList.length) {
            fetchRecoveryCodes();
        }
    }, [recoveryCodesList.length, fetchRecoveryCodes]);

    const RecoveryCodeIconComponent = codesAreVisible ? EyeOff : Eye;

    return (
        <Card>
            <div className="space-y-1">
                <h3 className="flex items-center gap-3 text-lg font-semibold text-gray-900 dark:text-white">
                    <LockKeyhole className="size-4" aria-hidden="true" />
                    رموز استرداد المصادقة الثنائية
                </h3>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                    تتيح لك رموز الاسترداد استعادة الوصول إذا فقدت جهاز
                    المصادقة الثنائية. احفظها في مدير كلمات مرور آمن.
                </p>
            </div>

            <div className="mt-4 flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between">
                <Button
                    onClick={toggleCodesVisibility}
                    className="w-fit gap-2"
                    aria-expanded={codesAreVisible}
                    aria-controls="recovery-codes-section"
                >
                    <RecoveryCodeIconComponent
                        className="size-4"
                        aria-hidden="true"
                    />
                    {codesAreVisible ? 'إخفاء' : 'عرض'} رموز الاسترداد
                </Button>

                {canRegenerateCodes && (
                    <Form
                        {...regenerateRecoveryCodes.form()}
                        options={{ preserveScroll: true }}
                        onSuccess={fetchRecoveryCodes}
                    >
                        {({ processing }) => (
                            <Button
                                color="light"
                                type="submit"
                                className="gap-2"
                                disabled={processing}
                                aria-describedby="regenerate-warning"
                            >
                                <RefreshCw className="h-4 w-4" />
                                إعادة توليد الرموز
                            </Button>
                        )}
                    </Form>
                )}
            </div>
            <div
                id="recovery-codes-section"
                className={`relative overflow-hidden transition-all duration-300 ${codesAreVisible ? 'h-auto opacity-100' : 'h-0 opacity-0'}`}
                aria-hidden={!codesAreVisible}
            >
                <div className="mt-3 space-y-3">
                    {errors?.length ? (
                        <AlertError errors={errors} />
                    ) : (
                        <>
                            <div
                                ref={codesSectionRef}
                                className="grid gap-1 rounded-lg bg-gray-100 p-4 font-mono text-sm dark:bg-gray-700"
                                role="list"
                                aria-label="رموز الاسترداد"
                            >
                                {recoveryCodesList.length ? (
                                    recoveryCodesList.map((code, index) => (
                                        <div
                                            key={index}
                                            role="listitem"
                                            className="select-text"
                                        >
                                            {code}
                                        </div>
                                    ))
                                ) : (
                                    <div
                                        className="space-y-2"
                                        aria-label="جاري تحميل رموز الاسترداد"
                                    >
                                        {Array.from(
                                            { length: 8 },
                                            (_, index) => (
                                                <div
                                                    key={index}
                                                    className="h-4 animate-pulse rounded bg-gray-300/40 dark:bg-gray-500/40"
                                                    aria-hidden="true"
                                                />
                                            ),
                                        )}
                                    </div>
                                )}
                            </div>

                            <div className="text-xs text-gray-500 select-none dark:text-gray-400">
                                <p id="regenerate-warning">
                                    يمكن استخدام كل رمز استرداد مرة واحدة فقط
                                    للوصول إلى حسابك، وسيُزال بعد الاستخدام. إذا
                                    احتجت المزيد، انقر{' '}
                                    <span className="font-bold">
                                        إعادة توليد الرموز
                                    </span>{' '}
                                    أعلاه.
                                </p>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </Card>
    );
}
