import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, TextInput } from 'flowbite-react';
import UserController from '@/actions/App/Http/Controllers/Settings/UserController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { create, index } from '@/routes/users';

type Props = {
    roles: Array<{ value: string; label: string }>;
};

export default function UsersCreate({ roles }: Props) {
    return (
        <>
            <Head title="إضافة مستخدم" />
            <div className="mx-auto max-w-2xl space-y-6">
                <Heading title="إضافة مستخدم" description="إنشاء حساب مستخدم جديد" />

                <Form {...UserController.store.form()} className="space-y-4">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">الاسم الكامل</Label>
                                <TextInput id="name" name="name" required />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="username">اسم المستخدم</Label>
                                <TextInput id="username" name="username" required />
                                <InputError message={errors.username} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">البريد الإلكتروني</Label>
                                <TextInput id="email" name="email" type="email" />
                                <InputError message={errors.email} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password">كلمة المرور</Label>
                                <PasswordInput id="password" name="password" required />
                                <InputError message={errors.password} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    تأكيد كلمة المرور
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="role">الدور</Label>
                                <Select id="role" name="role" required>
                                    {roles.map((role) => (
                                        <option key={role.value} value={role.value}>
                                            {role.label}
                                        </option>
                                    ))}
                                </Select>
                                <InputError message={errors.role} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="is_active">الحالة</Label>
                                <Select id="is_active" name="is_active" defaultValue="1">
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </Select>
                                <InputError message={errors.is_active} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    حفظ
                                </Button>
                                <Button color="light" href={index.url()} as="a">
                                    إلغاء
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

UsersCreate.layout = {
    breadcrumbs: [
        { title: 'المستخدمون', href: index() },
        { title: 'إضافة', href: create() },
    ],
};
