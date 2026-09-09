import { Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { login } from '@/routes';

export default function ResetPassword() {
    return (
        <>
            <Head title="إعادة تعيين كلمة المرور" />
            <div className="space-y-4 text-center">
                <p className="text-sm text-gray-600 dark:text-gray-300">
                    إعادة تعيين كلمة المرور غير مفعّلة. يرجى التواصل مع مدير
                    النظام.
                </p>
                <TextLink href={login()}>العودة لتسجيل الدخول</TextLink>
            </div>
        </>
    );
}

ResetPassword.layout = {
    title: 'إعادة تعيين كلمة المرور',
    description: 'هذه الميزة غير متاحة في النظام',
};
