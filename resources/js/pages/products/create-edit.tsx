import { Form, Head } from '@inertiajs/react';
import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { Edit, Package } from 'lucide-react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { ActiveStatusToggle } from '@/components/active-status-toggle';
import {
    AsyncSearchableSelect
    
} from '@/components/async-searchable-select';
import type {SearchableSelectOption} from '@/components/async-searchable-select';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { categories as categoryLookups, units as unitLookups } from '@/routes/lookups';
import { createEdit, index } from '@/routes/products';

type Props = {
    product: {
        id: number;
        code: string;
        barcode: string | null;
        name_ar: string;
        category_id: number;
        unit_id: number;
        notes: string | null;
        is_active: boolean;
    } | null;
    selected_category: SearchableSelectOption | null;
    selected_unit: SearchableSelectOption | null;
};

export default function ProductsCreateEdit({
    product,
    selected_category,
    selected_unit,
}: Props) {
    return (
        <>
            <Head title={product ? 'تعديل منتج' : 'إضافة منتج'} />
            <FormCard
                title={product ? 'تعديل منتج' : 'إضافة منتج'}
                description={
                    product
                        ? product.name_ar
                        : 'إنشاء منتج جديد وتعريف رمزه وصنفه ووحدة قياسه'
                }
                icon={product ? Edit : Package}
            >
                <Form
                    {...ProductController.storeUpdate.form(product?.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="code">رمز المنتج</Label>
                                    <TextInput
                                        id="code"
                                        name="code"
                                        required
                                        defaultValue={product?.code}
                                    />
                                    <InputError message={errors.code} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="barcode">الباركود</Label>
                                    <TextInput
                                        id="barcode"
                                        name="barcode"
                                        defaultValue={product?.barcode ?? ''}
                                    />
                                    <InputError message={errors.barcode} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name_ar">الاسم العربي</Label>
                                <TextInput
                                    id="name_ar"
                                    name="name_ar"
                                    required
                                    defaultValue={product?.name_ar}
                                />
                                <InputError message={errors.name_ar} />
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="category_id">الصنف</Label>
                                    <AsyncSearchableSelect
                                        id="category_id"
                                        name="category_id"
                                        required
                                        placeholder="اختر الصنف"
                                        searchPlaceholder="ابحث عن صنف..."
                                        defaultValue={
                                            product?.category_id ?? ''
                                        }
                                        initialOptions={
                                            selected_category
                                                ? [selected_category]
                                                : []
                                        }
                                        buildUrl={(search) =>
                                            categoryLookups.url(
                                                lookupQuery(search, {
                                                    include:
                                                        product?.category_id,
                                                }),
                                            )
                                        }
                                    />
                                    <InputError message={errors.category_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="unit_id">وحدة القياس</Label>
                                    <AsyncSearchableSelect
                                        id="unit_id"
                                        name="unit_id"
                                        required
                                        placeholder="اختر وحدة القياس"
                                        searchPlaceholder="ابحث عن وحدة..."
                                        defaultValue={product?.unit_id ?? ''}
                                        initialOptions={
                                            selected_unit
                                                ? [selected_unit]
                                                : []
                                        }
                                        buildUrl={(search) =>
                                            unitLookups.url(
                                                lookupQuery(search, {
                                                    include: product?.unit_id,
                                                }),
                                            )
                                        }
                                    />
                                    <InputError message={errors.unit_id} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">ملاحظات</Label>
                                <Textarea
                                    id="notes"
                                    name="notes"
                                    rows={4}
                                    defaultValue={product?.notes ?? ''}
                                />
                                <InputError message={errors.notes} />
                            </div>

                            <div className="md:max-w-xs">
                                <ActiveStatusToggle
                                    defaultChecked={product?.is_active ?? true}
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
                                    {product ? 'حفظ التعديلات' : 'إنشاء المنتج'}
                                </Button>
                            </FormActions>
                        </>
                    )}
                </Form>
            </FormCard>
        </>
    );
}

ProductsCreateEdit.layout = ({ product }: Props) => ({
    breadcrumbs: [
        { title: 'المنتجات', href: index() },
        product
            ? { title: 'تعديل', href: createEdit(product) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
