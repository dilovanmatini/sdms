import { Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import { useMemo } from 'react';
import PaymentReceiptController from '@/actions/App/Http/Controllers/PaymentReceiptController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import {
    type OpenInvoiceOption,
    type ReceiptAllocationDraft,
    ReceiptAllocationsEditor,
} from '@/components/receipt-allocations-editor';
import { create, index } from '@/routes/payment-receipts';

type Props = {
    distributors: Array<{ id: number; name: string }>;
    payment_methods: Array<{ value: string; label: string }>;
    open_invoices: OpenInvoiceOption[];
};

type ReceiptForm = {
    receipt_date: string;
    distributor_id: string;
    payment_method: string;
    notes: string;
    allocations: ReceiptAllocationDraft[];
};

export default function PaymentReceiptsCreate({
    distributors,
    payment_methods,
    open_invoices,
}: Props) {
    const form = useForm<ReceiptForm>({
        receipt_date: new Date().toISOString().slice(0, 10),
        distributor_id: '',
        payment_method: payment_methods[0]?.value ?? 'cash',
        notes: '',
        allocations: [{ sales_invoice_id: '', amount: '' }],
    });

    const invoicesForDistributor = useMemo(
        () =>
            open_invoices.filter(
                (invoice) =>
                    String(invoice.distributor_id) === form.data.distributor_id,
            ),
        [open_invoices, form.data.distributor_id],
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(PaymentReceiptController.store.url());
    };

    return (
        <>
            <Head title="إضافة سند قبض" />
            <div className="mx-auto max-w-3xl space-y-6">
                <Heading
                    title="إضافة سند قبض"
                    description="إنشاء مسودة سند قبض وتوزيعه على الفواتير"
                />

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="receipt_date">تاريخ السند</Label>
                        <TextInput
                            id="receipt_date"
                            type="date"
                            value={form.data.receipt_date}
                            onChange={(event) =>
                                form.setData('receipt_date', event.target.value)
                            }
                            required
                        />
                        <InputError message={form.errors.receipt_date} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="distributor_id">الموزع</Label>
                        <Select
                            id="distributor_id"
                            value={form.data.distributor_id}
                            onChange={(event) => {
                                form.setData(
                                    'distributor_id',
                                    event.target.value,
                                );
                                form.setData('allocations', [
                                    { sales_invoice_id: '', amount: '' },
                                ]);
                            }}
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
                        <Label htmlFor="payment_method">طريقة الدفع</Label>
                        <Select
                            id="payment_method"
                            value={form.data.payment_method}
                            onChange={(event) =>
                                form.setData(
                                    'payment_method',
                                    event.target.value,
                                )
                            }
                            required
                        >
                            {payment_methods.map((method) => (
                                <option key={method.value} value={method.value}>
                                    {method.label}
                                </option>
                            ))}
                        </Select>
                        <InputError message={form.errors.payment_method} />
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

                    <ReceiptAllocationsEditor
                        allocations={form.data.allocations}
                        invoices={invoicesForDistributor}
                        errors={form.errors}
                        onChange={(allocations) =>
                            form.setData('allocations', allocations)
                        }
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

PaymentReceiptsCreate.layout = {
    breadcrumbs: [
        { title: 'سندات القبض', href: index() },
        { title: 'إضافة', href: create() },
    ],
};
