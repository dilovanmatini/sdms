import { Head, Link, usePage } from '@inertiajs/react';
import { Button, Navbar, NavbarBrand } from 'flowbite-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { toUrl } from '@/lib/utils';
import { dashboard, login } from '@/routes';

export default function Welcome() {
    const { auth, name } = usePage().props;

    return (
        <>
            <Head title="مرحباً" />
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <Navbar
                    fluid
                    className="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
                >
                    <NavbarBrand as="div" className="gap-2">
                        <AppLogoIcon className="h-8 w-8" />
                        <span className="self-center text-xl font-semibold whitespace-nowrap text-gray-900 dark:text-white">
                            {name}
                        </span>
                    </NavbarBrand>
                    <div className="flex items-center gap-2">
                        {auth.user ? (
                            <Button as={Link} href={toUrl(dashboard())}>
                                الصفحة الرئيسية
                            </Button>
                        ) : (
                            <Button
                                as={Link}
                                href={toUrl(login())}
                                color="light"
                            >
                                تسجيل الدخول
                            </Button>
                        )}
                    </div>
                </Navbar>

                <main className="mx-auto flex max-w-4xl flex-col gap-8 px-6 py-16">
                    <div className="space-y-3">
                        <h1 className="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {name}
                        </h1>
                        <p className="max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                            إدارة المشتريات والمخزون والمبيعات وسندات القبض
                            وحسابات الموزعين في مكان واحد.
                        </p>
                    </div>
                </main>
            </div>
        </>
    );
}
