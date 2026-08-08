import { Form, Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import type { FormEvent } from 'react';
import { useMemo } from 'react';
import PaymentReceiptController from '@/actions/App/Http/Controllers/PaymentReceiptController';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import {
    type OpenInvoiceOption,
    type ReceiptAllocationDraft,
    ReceiptAllocationsEditor,
} from '@/components/receipt-allocations-editor';
import { edit, index } from '@/routes/payment-receipts';

type Props = {
    receipt: {
        id: number;
        number: string;
        receipt_date: string | null;
        distributor_id: number;
        payment_method: string;
        notes: string | null;
        status: 'draft' | 'posted';
        status_label: string;
        is_posted: boolean;
        allocations: Array<{
            sales_invoice_id: number;
            amount: string;
            invoice: {
                id: number;
                number: string;
                grand_total: string;
            } | null;
        }>;
    };
    distributors: Array<{ id: number; name: string }>;
    payment_methods: Array<{ value: string; label: string }>;
    open_invoices: OpenInvoiceOption[];
    can_edit: boolean;
    can_post: boolean;
};

type ReceiptForm = {
    receipt_date: string;
    distributor_id: string;
    payment_method: string;
    notes: string;
    allocations: ReceiptAllocationDraft[];
};

export default function PaymentReceiptsEdit({
    receipt,
    distributors,
    payment_methods,
    open_invoices,
    can_edit,
    can_post,
}: Props) {
    const form = useForm<ReceiptForm>({
        receipt_date: receipt.receipt_date ?? '',
        distributor_id: String(receipt.distributor_id),
        payment_method: receipt.payment_method,
        notes: receipt.notes ?? '',
        allocations: receipt.allocations.map((allocation) => ({
            sales_invoice_id: String(allocation.sales_invoice_id),
            amount: allocation.amount,
        })),
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

        if (!can_edit) {
            return;
        }

        form.put(PaymentReceiptController.update.url(receipt.id));
    };

    return (
        <>
            <Head
                title={
                    can_edit
                        ? `تعديل سند قبض ${receipt.number}`
                        : `عرض سند قبض ${receipt.number}`
                }
            />
            <div className="mx-auto max-w-3xl space-y-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title={can_edit ? 'تعديل سند قبض' : 'عرض سند قبض'}
                        description={receipt.number}
                    />
                    <DocumentStatusBadge
                        status={receipt.status}
                        label={receipt.status_label}
                    />
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="receipt_date">تاريخ السند</Label>
                        <TextInput
                            id="receipt_date"
                            type="date"
                            value={form.data.receipt_date}
                            disabled={!can_edit}
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
                            disabled={!can_edit}
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
                            disabled={!can_edit}
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
                            disabled={!can_edit}
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
                        readOnly={!can_edit}
                        onChange={(allocations) =>
                            form.setData('allocations', allocations)
                        }
                    />

                    {can_edit && (
                        <Button type="submit" disabled={form.processing}>
                            حفظ
                        </Button>
                    )}
                </form>

                <div className="flex flex-wrap gap-2">
                    {can_post && (
                        <Form
                            {...PaymentReceiptController.post.form(receipt.id)}
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    color="success"
                                    disabled={processing}
                                    onClick={(event) => {
                                        if (
                                            !window.confirm(
                                                'هل أنت متأكد من ترحيل سند القبض؟ لن يمكن تعديله بعد الترحيل.',
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

PaymentReceiptsEdit.layout = {
    breadcrumbs: [
        { title: 'سندات القبض', href: index() },
        { title: 'تعديل', href: edit(1) },
    ],
};
