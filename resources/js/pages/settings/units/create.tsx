import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, TextInput } from 'flowbite-react';
import UnitController from '@/actions/App/Http/Controllers/Settings/UnitController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { create, index } from '@/routes/units';

export default function UnitsCreate() {
    return (
        <>
            <Head title="إضافة وحدة قياس" />
            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="إضافة وحدة قياس"
                    description="إنشاء وحدة قياس جديدة للاستخدام في المنتجات"
                />

                <Form {...UnitController.store.form()} className="space-y-4">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">اسم الوحدة</Label>
                                <TextInput
                                    id="name"
                                    name="name"
                                    required
                                    placeholder="مثال: كرتون"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="symbol">الرمز (اختياري)</Label>
                                <TextInput
                                    id="symbol"
                                    name="symbol"
                                    placeholder="مثال: كرت"
                                />
                                <InputError message={errors.symbol} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="is_active">الحالة</Label>
                                <Select
                                    id="is_active"
                                    name="is_active"
                                    defaultValue="1"
                                >
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

UnitsCreate.layout = {
    breadcrumbs: [
        { title: 'وحدات القياس', href: index() },
        { title: 'إضافة', href: create() },
    ],
};
