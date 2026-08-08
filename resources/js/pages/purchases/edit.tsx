import { Form, Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import {
    PurchaseLinesEditor,
    type PurchaseLineDraft,
} from '@/components/purchase-lines-editor';
import { edit, index } from '@/routes/purchases';

type Props = {
    purchase: {
        id: number;
        number: string;
        purchase_date: string | null;
        supplier_id: number;
        notes: string | null;
        status: 'draft' | 'posted';
        status_label: string;
        is_posted: boolean;
        lines: Array<{
            product_id: number;
            quantity: string;
            product: { id: number; code: string; name_ar: string } | null;
        }>;
    };
    suppliers: Array<{ id: number; name: string }>;
    products: Array<{ id: number; code: string; name_ar: string }>;
    can_edit: boolean;
    can_post: boolean;
};

type PurchaseForm = {
    purchase_date: string;
    supplier_id: string;
    notes: string;
    lines: PurchaseLineDraft[];
};

export default function PurchasesEdit({
    purchase,
    suppliers,
    products,
    can_edit,
    can_post,
}: Props) {
    const form = useForm<PurchaseForm>({
        purchase_date: purchase.purchase_date ?? '',
        supplier_id: String(purchase.supplier_id),
        notes: purchase.notes ?? '',
        lines: purchase.lines.map((line) => ({
            product_id: String(line.product_id),
            quantity: line.quantity,
        })),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (!can_edit) {
            return;
        }

        form.put(PurchaseController.update.url(purchase.id));
    };

    return (
        <>
            <Head
                title={
                    can_edit
                        ? `تعديل مشترى ${purchase.number}`
                        : `عرض مشترى ${purchase.number}`
                }
            />
            <div className="mx-auto max-w-3xl space-y-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title={can_edit ? 'تعديل مشترى' : 'عرض مشترى'}
                        description={purchase.number}
                    />
                    <DocumentStatusBadge
                        status={purchase.status}
                        label={purchase.status_label}
                    />
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="purchase_date">تاريخ الشراء</Label>
                        <TextInput
                            id="purchase_date"
                            type="date"
                            value={form.data.purchase_date}
                            disabled={!can_edit}
                            onChange={(event) =>
                                form.setData(
                                    'purchase_date',
                                    event.target.value,
                                )
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
                            disabled={!can_edit}
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
                            disabled={!can_edit}
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
                        readOnly={!can_edit}
                        onChange={(lines) => form.setData('lines', lines)}
                    />

                    {can_edit && (
                        <Button type="submit" disabled={form.processing}>
                            حفظ
                        </Button>
                    )}
                </form>

                <div className="flex flex-wrap gap-2">
                    {can_post && (
                        <Form {...PurchaseController.post.form(purchase.id)}>
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    color="success"
                                    disabled={processing}
                                    onClick={(event) => {
                                        if (
                                            !window.confirm(
                                                'هل أنت متأكد من ترحيل هذا المشترى؟ لن يمكن تعديله بعد الترحيل.',
                                            )
                                        ) {
                                            event.preventDefault();
                                        }
                                    }}
                                >
                                    ترحيل
                                </Button>
                            )}
                        </Form>
                    )}
                    <Button color="light" href={index.url()} as="a">
                        رجوع
                    </Button>
                </div>
            </div>
        </>
    );
}

PurchasesEdit.layout = {
    breadcrumbs: [
        { title: 'المشتريات', href: index() },
        { title: 'تعديل', href: edit(1) },
    ],
};
