import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import DistributorController from '@/actions/App/Http/Controllers/DistributorController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { edit, index } from '@/routes/distributors';

type Props = {
    distributor: {
        id: number;
        name: string;
        contact_person: string | null;
        phone: string | null;
        address: string | null;
        credit_limit: string | null;
        notes: string | null;
        is_active: boolean;
    };
};

export default function DistributorsEdit({ distributor }: Props) {
    return (
        <>
            <Head title="تعديل موزع" />
            <div className="mx-auto max-w-2xl space-y-6">
                <Heading title="تعديل موزع" description={distributor.name} />

                <Form
                    {...DistributorController.update.form(distributor.id)}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">اسم الموزع</Label>
                                <TextInput
                                    id="name"
                                    name="name"
                                    required
                                    defaultValue={distributor.name}
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="contact_person">جهة الاتصال</Label>
                                <TextInput
                                    id="contact_person"
                                    name="contact_person"
                                    defaultValue={distributor.contact_person ?? ''}
                                />
                                <InputError message={errors.contact_person} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="phone">الهاتف</Label>
                                <TextInput
                                    id="phone"
                                    name="phone"
                                    defaultValue={distributor.phone ?? ''}
                                />
                                <InputError message={errors.phone} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="address">العنوان</Label>
                                <Textarea
                                    id="address"
                                    name="address"
                                    rows={2}
                                    defaultValue={distributor.address ?? ''}
                                />
                                <InputError message={errors.address} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="credit_limit">حد الائتمان</Label>
                                <TextInput
                                    id="credit_limit"
                                    name="credit_limit"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    defaultValue={distributor.credit_limit ?? ''}
                                />
                                <InputError message={errors.credit_limit} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="notes">ملاحظات</Label>
                                <Textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    defaultValue={distributor.notes ?? ''}
                                />
                                <InputError message={errors.notes} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="is_active">الحالة</Label>
                                <Select
                                    id="is_active"
                                    name="is_active"
                                    defaultValue={distributor.is_active ? '1' : '0'}
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

DistributorsEdit.layout = {
    breadcrumbs: [
        { title: 'الموزعون', href: index() },
        { title: 'تعديل', href: edit(1) },
    ],
};
