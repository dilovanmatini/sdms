import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, TextInput } from 'flowbite-react';
import UserController from '@/actions/App/Http/Controllers/Settings/UserController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { edit, index } from '@/routes/users';

type Props = {
    user: {
        id: number;
        name: string;
        username: string;
        email: string | null;
        role: string;
        is_active: boolean;
    };
    roles: Array<{ value: string; label: string }>;
};

export default function UsersEdit({ user, roles }: Props) {
    return (
        <>
            <Head title="تعديل مستخدم" />
            <div className="mx-auto max-w-2xl space-y-6">
                <Heading title="تعديل مستخدم" description={user.name} />

                <Form
                    {...UserController.update.form(user.id)}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">الاسم الكامل</Label>
                                <TextInput
                                    id="name"
                                    name="name"
                                    required
                                    defaultValue={user.name}
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="username">اسم المستخدم</Label>
                                <TextInput
                                    id="username"
                                    name="username"
                                    required
                                    defaultValue={user.username}
                                />
                                <InputError message={errors.username} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">البريد الإلكتروني</Label>
                                <TextInput
                                    id="email"
                                    name="email"
                                    type="email"
                                    defaultValue={user.email ?? ''}
                                />
                                <InputError message={errors.email} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    كلمة المرور (اتركها فارغة للإبقاء عليها)
                                </Label>
                                <PasswordInput id="password" name="password" />
                                <InputError message={errors.password} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    تأكيد كلمة المرور
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="role">الدور</Label>
                                <Select
                                    id="role"
                                    name="role"
                                    required
                                    defaultValue={user.role}
                                >
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
                                <Select
                                    id="is_active"
                                    name="is_active"
                                    defaultValue={user.is_active ? '1' : '0'}
                                >
                                    <option value="1">نشط</option>
                                    <option value="0">غير نشط</option>
                                </Select>
                                <InputError message={errors.is_active} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    حفظ التعديلات
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

UsersEdit.layout = {
    breadcrumbs: [
        { title: 'المستخدمون', href: index() },
        { title: 'تعديل', href: edit(1) },
    ],
};
