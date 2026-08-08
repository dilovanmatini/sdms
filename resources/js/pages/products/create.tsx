import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { create, index } from '@/routes/products';

type Props = {
    categories: Array<{ id: number; name: string }>;
    units: Array<{ id: number; name: string; symbol: string | null }>;
};

export default function ProductsCreate({ categories, units }: Props) {
    return (
        <>
            <Head title="إضافة منتج" />
            <div className="mx-auto max-w-2xl space-y-6">
                <Heading title="إضافة منتج" description="إنشاء منتج جديد" />

                <Form
                    {...ProductController.store.form()}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="code">رمز المنتج</Label>
                                <TextInput id="code" name="code" required />
                                <InputError message={errors.code} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="barcode">الباركود</Label>
                                <TextInput id="barcode" name="barcode" />
                                <InputError message={errors.barcode} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name_ar">الاسم العربي</Label>
                                <TextInput id="name_ar" name="name_ar" required />
                                <InputError message={errors.name_ar} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="category_id">الصنف</Label>
                                <Select id="category_id" name="category_id" required>
                                    <option value="">اختر الصنف</option>
                                    {categories.map((category) => (
                                        <option
                                            key={category.id}
                                            value={category.id}
                                        >
                                            {category.name}
                                        </option>
                                    ))}
                                </Select>
                                <InputError message={errors.category_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="unit_id">وحدة القياس</Label>
                                <Select id="unit_id" name="unit_id" required>
                                    <option value="">اختر وحدة القياس</option>
                                    {units.map((unit) => (
                                        <option key={unit.id} value={unit.id}>
                                            {unit.symbol
                                                ? `${unit.name} (${unit.symbol})`
                                                : unit.name}
                                        </option>
                                    ))}
                                </Select>
                                <InputError message={errors.unit_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">ملاحظات</Label>
                                <Textarea id="notes" name="notes" rows={3} />
                                <InputError message={errors.notes} />
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

ProductsCreate.layout = {
    breadcrumbs: [
        { title: 'المنتجات', href: index() },
        { title: 'إضافة', href: create() },
    ],
};
