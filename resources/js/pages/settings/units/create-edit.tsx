import { Form, Head } from '@inertiajs/react';
import { Button, Label, TextInput } from 'flowbite-react';
import { Edit, Ruler } from 'lucide-react';
import UnitController from '@/actions/App/Http/Controllers/Settings/UnitController';
import { ActiveStatusToggle } from '@/components/active-status-toggle';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import { createEdit, index } from '@/routes/units';

type Props = {
    unit: {
        id: number;
        name: string;
        symbol: string | null;
        is_active: boolean;
    } | null;
};

export default function UnitsCreateEdit({ unit }: Props) {
    return (
        <>
            <Head title={unit ? 'تعديل وحدة قياس' : 'إضافة وحدة قياس'} />
            <FormCard
                title={unit ? 'تعديل وحدة قياس' : 'إضافة وحدة قياس'}
                description={
                    unit
                        ? unit.name
                        : 'إنشاء وحدة قياس جديدة للاستخدام في المنتجات'
                }
                icon={unit ? Edit : Ruler}
            >
                <Form
                    {...UnitController.storeUpdate.form(unit?.id)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">اسم الوحدة</Label>
                                    <TextInput
                                        id="name"
                                        name="name"
                                        required
                                        placeholder="مثال: كرتون"
                                        defaultValue={unit?.name}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <ActiveStatusToggle
                                    defaultChecked={unit?.is_active ?? true}
                                    error={errors.is_active}
                                />

                                <div className="grid gap-2">
                                    <Label htmlFor="symbol">
                                        الرمز (اختياري)
                                    </Label>
                                    <TextInput
                                        id="symbol"
                                        name="symbol"
                                        placeholder="مثال: كرت"
                                        defaultValue={unit?.symbol ?? ''}
                                    />
                                    <InputError message={errors.symbol} />
                                </div>
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
                                    {unit ? 'حفظ التعديلات' : 'إنشاء الوحدة'}
                                </Button>
                            </FormActions>
                        </>
                    )}
                </Form>
            </FormCard>
        </>
    );
}

UnitsCreateEdit.layout = ({ unit }: Props) => ({
    breadcrumbs: [
        { title: 'وحدات القياس', href: index() },
        unit
            ? { title: 'تعديل', href: createEdit(unit) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
