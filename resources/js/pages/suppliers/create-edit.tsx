import { Form, Head } from '@inertiajs/react';
import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { Edit, Truck } from 'lucide-react';
import SupplierController from '@/actions/App/Http/Controllers/SupplierController';
import { ActiveStatusToggle } from '@/components/active-status-toggle';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import { createEdit, index } from '@/routes/suppliers';

type Props = {
    supplier: {
        id: number;
        name: string;
        contact_person: string | null;
        phone: string | null;
        address: string | null;
        notes: string | null;
        is_active: boolean;
    } | null;
};

export default function SuppliersCreateEdit({ supplier }: Props) {
    return (
        <>
            <Head title={supplier ? 'تعديل مورد' : 'إضافة مورد'} />
            <FormCard
                title={supplier ? 'تعديل مورد' : 'إضافة مورد'}
                description={
                    supplier
                        ? supplier.name
                        : 'إنشاء مورد جديد وإدارة بيانات الاتصال'
                }
                icon={supplier ? Edit : Truck}
            >
                <Form
                    {...SupplierController.storeUpdate.form(supplier?.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">اسم المورد</Label>
                                    <TextInput
                                        id="name"
                                        name="name"
                                        required
                                        defaultValue={supplier?.name}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <ActiveStatusToggle
                                    defaultChecked={supplier?.is_active ?? true}
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
                                            supplier?.contact_person ?? ''
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
                                        defaultValue={supplier?.phone ?? ''}
                                    />
                                    <InputError message={errors.phone} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="address">العنوان</Label>
                                <Textarea
                                    id="address"
                                    name="address"
                                    rows={2}
                                    defaultValue={supplier?.address ?? ''}
                                />
                                <InputError message={errors.address} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">ملاحظات</Label>
                                <Textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    defaultValue={supplier?.notes ?? ''}
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
                                    {supplier
                                        ? 'حفظ التعديلات'
                                        : 'إنشاء المورد'}
                                </Button>
                            </FormActions>
                        </>
                    )}
                </Form>
            </FormCard>
        </>
    );
}

SuppliersCreateEdit.layout = ({ supplier }: Props) => ({
    breadcrumbs: [
        { title: 'الموردون', href: index() },
        supplier
            ? { title: 'تعديل', href: createEdit(supplier) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
