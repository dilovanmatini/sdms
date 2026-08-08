import { Form, Head } from '@inertiajs/react';
import { Button, Label, Select, TextInput } from 'flowbite-react';
import UnitController from '@/actions/App/Http/Controllers/Settings/UnitController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { edit, index } from '@/routes/units';

type Props = {
    unit: {
        id: number;
        name: string;
        symbol: string | null;
        is_active: boolean;
    };
};

export default function UnitsEdit({ unit }: Props) {
    return (
        <>
            <Head title="تعديل وحدة قياس" />
            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="تعديل وحدة قياس"
                    description={unit.name}
                />

                <Form
                    {...UnitController.update.form(unit.id)}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">اسم الوحدة</Label>
                                <TextInput
                                    id="name"
                                    name="name"
                                    required
                                    defaultValue={unit.name}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="symbol">الرمز (اختياري)</Label>
                                <TextInput
                                    id="symbol"
                                    name="symbol"
                                    defaultValue={unit.symbol ?? ''}
                                />
                                <InputError message={errors.symbol} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="is_active">الحالة</Label>
                                <Select
                                    id="is_active"
                                    name="is_active"
                                    defaultValue={unit.is_active ? '1' : '0'}
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

UnitsEdit.layout = {
    breadcrumbs: [
        { title: 'وحدات القياس', href: index() },
        { title: 'تعديل', href: edit(1) },
    ],
};
