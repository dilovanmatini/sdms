import { Form, Head, usePage } from '@inertiajs/react';
import { Button, Label, TextInput } from 'flowbite-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { edit } from '@/routes/profile';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function Profile() {
    const { auth } = usePage<PageProps>().props;

    return (
        <>
            <Head title="إعدادات الملف الشخصي" />

            <h1 className="sr-only">إعدادات الملف الشخصي</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="الملف الشخصي"
                    description="تحديث الاسم والبريد الإلكتروني"
                />

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">الاسم الكامل</Label>

                                <TextInput
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="الاسم الكامل"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="username">اسم المستخدم</Label>

                                <TextInput
                                    id="username"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.username}
                                    name="username"
                                    disabled
                                    autoComplete="username"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    البريد الإلكتروني (اختياري)
                                </Label>

                                <TextInput
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email ?? ''}
                                    name="email"
                                    autoComplete="email"
                                    placeholder="البريد الإلكتروني"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    حفظ
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'إعدادات الملف الشخصي',
            href: edit(),
        },
    ],
};
