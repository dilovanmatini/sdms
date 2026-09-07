import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import { Edit, Tags } from 'lucide-react';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import { createEdit, index } from '@/routes/categories';

type Props = {
    category: {
        id: number;
        name: string;
        description: string | null;
        is_active: boolean;
    } | null;
};

export default function CategoriesCreateEdit({ category }: Props) {
    return (
        <>
            <Head title={category ? 'تعديل صنف' : 'إضافة صنف'} />
            <FormCard
                title={category ? 'تعديل صنف' : 'إضافة صنف'}
                description={category ? category.name : 'إنشاء صنف منتج جديد وتنظيم المنتجات ضمن مجموعة واضحة'}
                icon={category ? Edit : Tags}
            >
                <Form
                    {...CategoryController.storeUpdate.form(category?.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">اسم الصنف</Label>
                                    <TextInput
                                        id="name"
                                        name="name"
                                        required
                                        defaultValue={category?.name}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="is_active">الحالة</Label>
                                    <Select
                                        id="is_active"
                                        name="is_active"
                                        defaultValue={category?.is_active ? '1' : '0'}
                                    >
                                        <option value="1">نشط</option>
                                        <option value="0">غير نشط</option>
                                    </Select>
                                    <InputError message={errors.is_active} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">الوصف</Label>
                                <Textarea
                                    id="description"
                                    name="description"
                                    rows={4}
                                    defaultValue={category?.description ?? ''}
                                />
                                <InputError message={errors.description} />
                            </div>

                            <FormActions>
                                <Button type="submit" disabled={processing}>
                                    {category ? 'حفظ التعديلات' : 'إنشاء الصنف'}
                                </Button>
                                <Button
                                    color="light"
                                    href={index.url()}
                                    as="a"
                                >
                                    إلغاء
                                </Button>
                            </FormActions>
                        </>
                    )}
                </Form>
            </FormCard>
        </>
    );
}

CategoriesCreateEdit.layout = ({ category }: Props) => ({
    breadcrumbs: [
        { title: 'الأصناف', href: index() },
        category
            ? { title: 'تعديل', href: createEdit(category) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
