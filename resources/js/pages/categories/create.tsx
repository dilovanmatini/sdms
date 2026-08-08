import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { create, index } from '@/routes/categories';

export default function CategoriesCreate() {
    return (
        <>
            <Head title="إضافة صنف" />
            <div className="mx-auto max-w-2xl space-y-6">
                <Heading title="إضافة صنف" description="إنشاء صنف منتج جديد" />

                <Form
                    {...CategoryController.store.form()}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">اسم الصنف</Label>
                                <TextInput id="name" name="name" required />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">الوصف</Label>
                                <Textarea id="description" name="description" rows={3} />
                                <InputError message={errors.description} />
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
                                <Button
                                    color="light"
                                    href={index.url()}
                                    as="a"
                                >
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

CategoriesCreate.layout = {
    breadcrumbs: [
        { title: 'الأصناف', href: index() },
        { title: 'إضافة', href: create() },
    ],
};
