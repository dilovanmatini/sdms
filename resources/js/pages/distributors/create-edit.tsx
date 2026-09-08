import { Form, Head } from '@inertiajs/react';
import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { Edit, Users } from 'lucide-react';
import DistributorController from '@/actions/App/Http/Controllers/DistributorController';
import { ActiveStatusToggle } from '@/components/active-status-toggle';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import { createEdit, index } from '@/routes/distributors';

type Props = {
    distributor: {
        id: number;
        name: string;
        contact_person: string | null;
        phone: string | null;
        address: string | null;
        credit_limit: number | null;
        notes: string | null;
        is_active: boolean;
    } | null;
};

export default function DistributorsCreateEdit({ distributor }: Props) {
    return (
        <>
            <Head title={distributor ? 'تعديل موزع' : 'إضافة موزع'} />
            <FormCard
                title={distributor ? 'تعديل موزع' : 'إضافة موزع'}
                description={
                    distributor
                        ? distributor.name
                        : 'إنشاء موزع جديد وإدارة بيانات الاتصال والائتمان'
                }
                icon={distributor ? Edit : Users}
            >
                <Form
                    {...DistributorController.storeUpdate.form(distributor?.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">اسم الموزع</Label>
                                    <TextInput
                                        id="name"
                                        name="name"
                                        required
                                        defaultValue={distributor?.name}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <ActiveStatusToggle
                                    defaultChecked={
                                        distributor?.is_active ?? true
                                    }
                                    error={errors.is_active}
                                />

                                <div className="grid gap-2">
                                    <Label htmlFor="contact_person">
                                        جهة الاتصال
                                    </Label>
                                    <TextInput
                                        id="contact_person"
                                        name="contact_person"
                                        defaultValue={
                                            distributor?.contact_person ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.contact_person}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">الهاتف</Label>
                                    <TextInput
                                        id="phone"
                                        name="phone"
                                        defaultValue={distributor?.phone ?? ''}
                                    />
                                    <InputError message={errors.phone} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="credit_limit">
                                        حد الائتمان
                                    </Label>
                                    <TextInput
                                        id="credit_limit"
                                        name="credit_limit"
                                        type="number"
                                        step="1"
                                        min="0"
                                        defaultValue={
                                            distributor?.credit_limit ?? ''
                                        }
                                    />
                                    <InputError message={errors.credit_limit} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="address">العنوان</Label>
                                <Textarea
                                    id="address"
                                    name="address"
                                    rows={2}
                                    defaultValue={distributor?.address ?? ''}
                                />
                                <InputError message={errors.address} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">ملاحظات</Label>
                                <Textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    defaultValue={distributor?.notes ?? ''}
                                />
                                <InputError message={errors.notes} />
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
                                    {distributor
                                        ? 'حفظ التعديلات'
                                        : 'إنشاء الموزع'}
                                </Button>
                            </FormActions>
                        </>
                    )}
                </Form>
            </FormCard>
        </>
    );
}

DistributorsCreateEdit.layout = ({ distributor }: Props) => ({
    breadcrumbs: [
        { title: 'الموزعون', href: index() },
        distributor
            ? { title: 'تعديل', href: createEdit(distributor) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
