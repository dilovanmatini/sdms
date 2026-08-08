import { Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import {
    PurchaseLinesEditor,
    type PurchaseLineDraft,
} from '@/components/purchase-lines-editor';
import { create, index } from '@/routes/purchases';

type Props = {
    suppliers: Array<{ id: number; name: string }>;
    products: Array<{ id: number; code: string; name_ar: string }>;
};

type PurchaseForm = {
    purchase_date: string;
    supplier_id: string;
    notes: string;
    lines: PurchaseLineDraft[];
};

export default function PurchasesCreate({ suppliers, products }: Props) {
    const form = useForm<PurchaseForm>({
        purchase_date: new Date().toISOString().slice(0, 10),
        supplier_id: '',
        notes: '',
        lines: [{ product_id: '', quantity: '' }],
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(PurchaseController.store.url());
    };

    return (
        <>
            <Head title="إضافة مشترى" />
            <div className="mx-auto max-w-3xl space-y-6">
                <Heading
                    title="إضافة مشترى"
                    description="إنشاء مسودة مشترى جديدة"
                />

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="purchase_date">تاريخ الشراء</Label>
                        <TextInput
                            id="purchase_date"
                            type="date"
                            value={form.data.purchase_date}
                            onChange={(event) =>
                                form.setData('purchase_date', event.target.value)
                            }
                            required
                        />
                        <InputError message={form.errors.purchase_date} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="supplier_id">المورد</Label>
                        <Select
                            id="supplier_id"
                            value={form.data.supplier_id}
                            onChange={(event) =>
                                form.setData('supplier_id', event.target.value)
                            }
                            required
                        >
                            <option value="">اختر المورد</option>
                            {suppliers.map((supplier) => (
                                <option key={supplier.id} value={supplier.id}>
                                    {supplier.name}
                                </option>
                            ))}
                        </Select>
                        <InputError message={form.errors.supplier_id} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="notes">ملاحظات</Label>
                        <Textarea
                            id="notes"
                            rows={3}
                            value={form.data.notes}
                            onChange={(event) =>
                                form.setData('notes', event.target.value)
                            }
                        />
                        <InputError message={form.errors.notes} />
                    </div>

                    <PurchaseLinesEditor
                        lines={form.data.lines}
                        products={products}
                        errors={form.errors}
                        onChange={(lines) => form.setData('lines', lines)}
                    />

                    <div className="flex gap-2">
                        <Button type="submit" disabled={form.processing}>
                            حفظ
                        </Button>
                        <Button color="light" href={index.url()} as="a">
                            إلغاء
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

PurchasesCreate.layout = {
    breadcrumbs: [
        { title: 'المشتريات', href: index() },
        { title: 'إضافة', href: create() },
    ],
};
