import { Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import SalesInvoiceController from '@/actions/App/Http/Controllers/SalesInvoiceController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import {
    calculateInvoiceSubtotal,
    SalesInvoiceLinesEditor,
    type SalesInvoiceLineDraft,
} from '@/components/sales-invoice-lines-editor';
import { create, index } from '@/routes/sales-invoices';

type Props = {
    distributors: Array<{ id: number; name: string }>;
    products: Array<{ id: number; code: string; name_ar: string }>;
};

type InvoiceForm = {
    invoice_date: string;
    distributor_id: string;
    notes: string;
    discount: string;
    lines: SalesInvoiceLineDraft[];
};

export default function SalesInvoicesCreate({ distributors, products }: Props) {
    const form = useForm<InvoiceForm>({
        invoice_date: new Date().toISOString().slice(0, 10),
        distributor_id: '',
        notes: '',
        discount: '0',
        lines: [{ product_id: '', quantity: '', unit_price: '' }],
    });

    const subtotal = calculateInvoiceSubtotal(form.data.lines);
    const discount = Number(form.data.discount) || 0;
    const grandTotal = Math.max(subtotal - discount, 0);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(SalesInvoiceController.store.url());
    };

    return (
        <>
            <Head title="إضافة فاتورة مبيعات" />
            <div className="mx-auto max-w-4xl space-y-6">
                <Heading
                    title="إضافة فاتورة مبيعات"
                    description="إنشاء مسودة فاتورة جديدة"
                />

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="invoice_date">تاريخ الفاتورة</Label>
                        <TextInput
                            id="invoice_date"
                            type="date"
                            value={form.data.invoice_date}
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

SalesInvoicesCreate.layout = {
    breadcrumbs: [
        { title: 'فواتير المبيعات', href: index() },
        { title: 'إضافة', href: create() },
    ],
};
