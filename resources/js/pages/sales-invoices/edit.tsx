import { Form, Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import SalesInvoiceController from '@/actions/App/Http/Controllers/SalesInvoiceController';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import {
    calculateInvoiceSubtotal,
    SalesInvoiceLinesEditor,
    type SalesInvoiceLineDraft,
} from '@/components/sales-invoice-lines-editor';
import { edit, index } from '@/routes/sales-invoices';

type Props = {
    invoice: {
        id: number;
        number: string;
        invoice_date: string | null;
        distributor_id: number;
        notes: string | null;
        subtotal: string;
        discount: string;
        grand_total: string;
        status: 'draft' | 'posted';
        status_label: string;
        is_posted: boolean;
        lines: Array<{
            product_id: number;
            quantity: string;
            unit_price: string;
            line_total: string;
            product: { id: number; code: string; name_ar: string } | null;
        }>;
    };
    distributors: Array<{ id: number; name: string }>;
    products: Array<{ id: number; code: string; name_ar: string }>;
    can_edit: boolean;
    can_post: boolean;
};

type InvoiceForm = {
    invoice_date: string;
    distributor_id: string;
    notes: string;
    discount: string;
    lines: SalesInvoiceLineDraft[];
};

export default function SalesInvoicesEdit({
    invoice,
    distributors,
    products,
    can_edit,
    can_post,
}: Props) {
    const form = useForm<InvoiceForm>({
        invoice_date: invoice.invoice_date ?? '',
        distributor_id: String(invoice.distributor_id),
        notes: invoice.notes ?? '',
        discount: invoice.discount,
        lines: invoice.lines.map((line) => ({
            product_id: String(line.product_id),
            quantity: line.quantity,
            unit_price: line.unit_price,
        })),
    });

    const subtotal = can_edit
        ? calculateInvoiceSubtotal(form.data.lines)
        : Number(invoice.subtotal);
    const discount = can_edit
        ? Number(form.data.discount) || 0
        : Number(invoice.discount);
    const grandTotal = can_edit
        ? Math.max(subtotal - discount, 0)
        : Number(invoice.grand_total);

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (!can_edit) {
            return;
        }

        form.put(SalesInvoiceController.update.url(invoice.id));
    };

    return (
        <>
            <Head
                title={
                    can_edit
                        ? `تعديل فاتورة ${invoice.number}`
                        : `عرض فاتورة ${invoice.number}`
                }
            />
            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title={can_edit ? 'تعديل فاتورة' : 'عرض فاتورة'}
                        description={invoice.number}
                    />
                    <DocumentStatusBadge
                        status={invoice.status}
                        label={invoice.status_label}
                    />
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="invoice_date">تاريخ الفاتورة</Label>
                        <TextInput
                            id="invoice_date"
                            type="date"
                            value={form.data.invoice_date}
                            disabled={!can_edit}
                            onChange={(event) =>
                                form.setData('invoice_date', event.target.value)
                            }
                            required
                        />
                        <InputError message={form.errors.invoice_date} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="distributor_id">الموزع</Label>
                        <Select
                            id="distributor_id"
                            value={form.data.distributor_id}
                            disabled={!can_edit}
                            onChange={(event) =>
                                form.setData(
                                    'distributor_id',
                                    event.target.value,
                                )
                            }
                            required
                        >
                            <option value="">اختر الموزع</option>
                            {distributors.map((distributor) => (
                                <option
                                    key={distributor.id}
                                    value={distributor.id}
                                >
                                    {distributor.name}
                                </option>
                            ))}
                        </Select>
                        <InputError message={form.errors.distributor_id} />
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

                    <SalesInvoiceLinesEditor
                        lines={form.data.lines}
                        products={products}
                        errors={form.errors}
                        readOnly={!can_edit}
                        onChange={(lines) => form.setData('lines', lines)}
                    />

                    <div className="grid gap-4 rounded-lg border border-gray-200 p-4 sm:grid-cols-3 dark:border-gray-700">
                        <div className="grid gap-2">
                            <Label>المجموع الفرعي</Label>
                            <TextInput
                                value={subtotal.toFixed(2)}
                                readOnly
                                disabled
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="discount">الخصم</Label>
                            <TextInput
                                id="discount"
                                type="number"
                                min="0"
                                step="0.01"
                                value={form.data.discount}
                                disabled={!can_edit}
                                onChange={(event) =>
                                    form.setData('discount', event.target.value)
                                }
                            />
                            <InputError message={form.errors.discount} />
                        </div>
                        <div className="grid gap-2">
                            <Label>الإجمالي</Label>
                            <TextInput
                                value={grandTotal.toFixed(2)}
                                readOnly
                                disabled
                                className="font-semibold"
                            />
                        </div>
                    </div>

                    {can_edit && (
                        <Button type="submit" disabled={form.processing}>
                            حفظ
                        </Button>
                    )}
                </form>

                <div className="flex flex-wrap gap-2">
                    {can_post && (
                        <Form
                            {...SalesInvoiceController.post.form(invoice.id)}
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    color="success"
                                    disabled={processing}
                                    onClick={(event) => {
                                        if (
                                            !window.confirm(
                                                'هل أنت متأكد من ترحيل هذه الفاتورة؟ لن يمكن تعديلها بعد الترحيل.',
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

SalesInvoicesEdit.layout = {
    breadcrumbs: [
        { title: 'فواتير المبيعات', href: index() },
        { title: 'تعديل', href: edit(1) },
    ],
};
