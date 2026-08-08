import { Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { login } from '@/routes';

export default function ForgotPassword() {
    return (
        <>
            <Head title="استعادة كلمة المرور" />
            <div className="space-y-4 text-center">
                <p className="text-sm text-gray-600 dark:text-gray-300">
                    استعادة كلمة المرور غير مفعّلة. يرجى التواصل مع مدير النظام.
                </p>
                <TextLink href={login()}>العودة لتسجيل الدخول</TextLink>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'استعادة كلمة المرور',
    description: 'هذه الميزة غير متاحة في النظام',
};
