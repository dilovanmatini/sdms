import { Form, Head, usePage } from '@inertiajs/react';
import { Button, Label, TextInput } from 'flowbite-react';
import { UserRound } from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import { FormActions, FormCard } from '@/components/form-card';
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

            <div className="space-y-6">
                <FormCard
                    title="الملف الشخصي"
                    description="تحديث الاسم والبريد الإلكتروني"
                    icon={UserRound}
                >
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
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="الاسم الكامل"
                                    />

                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="username">
                                        اسم المستخدم
                                    </Label>

                                    <TextInput
                                        id="username"
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
                                        defaultValue={auth.user.email ?? ''}
                                        name="email"
                                        autoComplete="email"
                                        placeholder="البريد الإلكتروني"
                                    />

                                    <InputError message={errors.email} />
                                </div>

                                <FormActions>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="update-profile-button"
                                    >
                                        حفظ
                                    </Button>
                                </FormActions>
                            </>
                        )}
                    </Form>
                </FormCard>

                <DeleteUser />
            </div>
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
