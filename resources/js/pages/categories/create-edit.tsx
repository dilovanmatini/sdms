import { Form, Head } from '@inertiajs/react';
import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { Edit, Tags } from 'lucide-react';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import { ActiveStatusToggle } from '@/components/active-status-toggle';
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

                                <ActiveStatusToggle
                                    defaultChecked={category?.is_active ?? true}
                                    error={errors.is_active}
                                />
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
                                    {category ? 'حفظ التعديلات' : 'إنشاء الصنف'}
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
