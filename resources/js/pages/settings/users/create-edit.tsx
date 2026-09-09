import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, TextInput } from 'flowbite-react';
import { Edit, UserCog } from 'lucide-react';
import UserController from '@/actions/App/Http/Controllers/Settings/UserController';
import { ActiveStatusToggle } from '@/components/active-status-toggle';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { createEdit, index } from '@/routes/users';

type Props = {
    user: {
        id: number;
        name: string;
        username: string;
        email: string | null;
        role: string;
        is_active: boolean;
    } | null;
    roles: Array<{ value: string; label: string }>;
};

export default function UsersCreateEdit({ user, roles }: Props) {
    return (
        <>
            <Head title={user ? 'تعديل مستخدم' : 'إضافة مستخدم'} />
            <FormCard
                title={user ? 'تعديل مستخدم' : 'إضافة مستخدم'}
                description={user ? user.name : 'إنشاء حساب مستخدم جديد'}
                icon={user ? Edit : UserCog}
            >
                <Form
                    {...UserController.storeUpdate.form(user?.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">الاسم الكامل</Label>
                                    <TextInput
                                        id="name"
                                        name="name"
                                        required
                                        defaultValue={user?.name}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="username">
                                        اسم المستخدم
                                    </Label>
                                    <TextInput
                                        id="username"
                                        name="username"
                                        required
                                        defaultValue={user?.username}
                                    />
                                    <InputError message={errors.username} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">
                                        البريد الإلكتروني
                                    </Label>
                                    <TextInput
                                        id="email"
                                        name="email"
                                        type="email"
                                        defaultValue={user?.email ?? ''}
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="role">الدور</Label>
                                    <Select
                                        id="role"
                                        name="role"
                                        required
                                        defaultValue={
                                            user?.role ?? roles[0]?.value
                                        }
                                    >
                                        {roles.map((role) => (
                                            <option
                                                key={role.value}
                                                value={role.value}
                                            >
                                                {role.label}
                                            </option>
                                        ))}
                                    </Select>
                                    <InputError message={errors.role} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        {user
                                            ? 'كلمة المرور (اتركها فارغة للإبقاء عليها)'
                                            : 'كلمة المرور'}
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        required={!user}
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        تأكيد كلمة المرور
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        required={!user}
                                    />
                                </div>

                                <ActiveStatusToggle
                                    defaultChecked={user?.is_active ?? true}
                                    error={errors.is_active}
                                />
                            </div>

                            <FormActions
                                secondary={
                                    <Button
                                        color="light"
                                        href={index.url()}
                                        as="a"
                                    >
                                        رجوع
                                    </Button>
                                }
                            >
                                <Button type="submit" disabled={processing}>
                                    {user ? 'حفظ التعديلات' : 'إنشاء المستخدم'}
                                </Button>
                            </FormActions>
                        </>
                    )}
                </Form>
            </FormCard>
        </>
    );
}

UsersCreateEdit.layout = ({ user }: Props) => ({
    breadcrumbs: [
        { title: 'المستخدمون', href: index() },
        user
            ? { title: 'تعديل', href: createEdit(user) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
