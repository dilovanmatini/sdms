import { Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import { Edit, Printer, Wallet } from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo } from 'react';
import PaymentReceiptController from '@/actions/App/Http/Controllers/PaymentReceiptController';
import {
    AsyncSearchableSelect,
    type SearchableSelectOption,
} from '@/components/async-searchable-select';
import { ConfirmActionButton } from '@/components/confirm-action-button';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import {
    ReceiptAllocationsEditor,
    type ReceiptAllocationDraft,
} from '@/components/receipt-allocations-editor';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { distributors as distributorLookups } from '@/routes/lookups';
import { createEdit, index, print as paymentReceiptsPrint } from '@/routes/payment-receipts';

type Props = {
    receipt: {
        id: number;
        number: string;
        receipt_date: string | null;
        distributor_id: number;
        payment_method: string;
        notes: string | null;
        status: 'draft' | 'posted' | 'cancelled';
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
    } | null;
    selected_distributor: SearchableSelectOption | null;
    selected_invoices: SearchableSelectOption[];
    payment_methods: Array<{ value: string; label: string }>;
    can_edit: boolean;
    can_post: boolean;
    can_cancel: boolean;
    can_print: boolean;
};

type ReceiptForm = {
    receipt_date: string;
    distributor_id: string;
    payment_method: string;
    notes: string;
    allocations: ReceiptAllocationDraft[];
};

export default function PaymentReceiptsCreateEdit({
    receipt,
    selected_distributor,
    selected_invoices,
    payment_methods,
    can_edit,
    can_post,
    can_cancel,
    can_print,
}: Props) {
    const form = useForm<ReceiptForm>({
        receipt_date:
            receipt?.receipt_date ?? new Date().toISOString().slice(0, 10),
        distributor_id: receipt
            ? String(receipt.distributor_id)
            : selected_distributor
              ? String(selected_distributor.value)
              : '',
        payment_method:
            receipt?.payment_method ?? payment_methods[0]?.value ?? 'cash',
        notes: receipt?.notes ?? '',
                        allocations: receipt
                            ? receipt.allocations.map((allocation) => ({
                                  sales_invoice_id: String(
                                      allocation.sales_invoice_id,
                                  ),
                                  amount: allocation.amount,
                                  grand_total:
                                      allocation.invoice?.grand_total,
                              }))
                            : [{ sales_invoice_id: '', amount: '' }],
    });

    const invoiceIncludeIds = useMemo(
        () =>
            form.data.allocations
                .map((allocation) => allocation.sales_invoice_id)
                .filter(Boolean),
        [form.data.allocations],
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (!can_edit) {
            return;
        }

        form.post(PaymentReceiptController.storeUpdate.url(receipt?.id));
    };

    const isEdit = receipt !== null;

    return (
        <>
            <Head
                title={
                    isEdit
                        ? can_edit
                            ? `تعديل سند قبض ${receipt.number}`
                            : `عرض سند قبض ${receipt.number}`
                        : 'إضافة سند قبض'
                }
            />
            <FormCard
                title={
                    isEdit
                        ? can_edit
                            ? 'تعديل سند قبض'
                            : 'عرض سند قبض'
                        : 'إضافة سند قبض'
                }
                description={
                    isEdit
                        ? receipt.number
                        : 'إنشاء مسودة سند قبض وتوزيعه على الفواتير'
                }
                icon={isEdit ? Edit : Wallet}
                actions={
                    isEdit ? (
                        <DocumentStatusBadge
                            status={receipt.status}
                            label={receipt.status_label}
                        />
                    ) : undefined
                }
            >
                <form
                    id="payment-receipt-form"
                    onSubmit={submit}
                    className="space-y-6"
                >
                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="receipt_date">تاريخ السند</Label>
                            <TextInput
                                id="receipt_date"
                                type="date"
                                value={form.data.receipt_date}
                                disabled={!can_edit}
                                onChange={(event) =>
                                    form.setData(
                                        'receipt_date',
                                        event.target.value,
                                    )
                                }
                                required
                            />
                            <InputError message={form.errors.receipt_date} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="distributor_id">الموزع</Label>
                            <AsyncSearchableSelect
                                id="distributor_id"
                                name="distributor_id"
                                required
                                disabled={!can_edit}
                                placeholder="اختر الموزع"
                                searchPlaceholder="ابحث عن موزع..."
                                value={form.data.distributor_id}
                                initialOptions={
                                    selected_distributor
                                        ? [selected_distributor]
                                        : []
                                }
                                buildUrl={(search) =>
                                    distributorLookups.url(
                                        lookupQuery(search, {
                                            include:
                                                form.data.distributor_id ||
                                                undefined,
                                        }),
                                    )
                                }
                                onChange={(value) => {
                                    form.setData('distributor_id', value);
                                    form.setData('allocations', [
                                        { sales_invoice_id: '', amount: '' },
                                    ]);
                                }}
                            />
                            <InputError message={form.errors.distributor_id} />
                        </div>
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
                        distributorId={form.data.distributor_id}
                        selectedInvoices={selected_invoices}
                        invoiceIncludeIds={invoiceIncludeIds}
                        errors={form.errors}
                        readOnly={!can_edit}
                        onChange={(allocations) =>
                            form.setData('allocations', allocations)
                        }
                    />

                </form>

                {can_edit && (
                    <FormActions
                        className="mt-6"
                        secondary={
                            <Button color="light" href={index.url()} as="a">
                                رجوع
                            </Button>
                        }
                    >
                        <Button
                            type="submit"
                            form="payment-receipt-form"
                            disabled={form.processing}
                        >
                            {isEdit ? 'حفظ التعديلات' : 'إنشاء سند القبض'}
                        </Button>
                        {can_post && receipt && (
                            <ConfirmActionButton
                                href={PaymentReceiptController.post.url(
                                    receipt.id,
                                )}
                                color="green"
                                confirmColor="green"
                                confirmTitle="تأكيد نهائي"
                                confirmMessage="هل تريد تأكيد سند القبض نهائياً؟ لن يمكن تعديله بعد ذلك."
                                confirmLabel="تأكيد نهائي"
                                confirmingLabel="جارٍ التأكيد..."
                            >
                                تأكيد نهائي
                            </ConfirmActionButton>
                        )}
                    </FormActions>
                )}

                {!can_edit && isEdit && (
                    <FormActions
                        className="mt-6"
                        secondary={
                            <>
                                {can_cancel && (
                                    <ConfirmActionButton
                                        href={PaymentReceiptController.cancel.url(
                                            receipt.id,
                                        )}
                                        color="red"
                                        confirmColor="red"
                                        confirmTitle="إلغاء سند القبض"
                                        confirmMessage="هل أنت متأكد من إلغاء سند القبض النشط؟ سيتم عكس أثره على الذمم."
                                        confirmLabel="إلغاء السند"
                                        confirmingLabel="جارٍ الإلغاء..."
                                    >
                                        إلغاء السند
                                    </ConfirmActionButton>
                                )}
                                <Button color="light" href={index.url()} as="a">
                                    رجوع
                                </Button>
                            </>
                        }
                    >
                        {can_print && (
                            <Button
                                color="dark"
                                href={paymentReceiptsPrint.url(receipt.id)}
                                as="a"
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-2"
                            >
                                <Printer className="h-4 w-4" />
                                طباعة
                            </Button>
                        )}
                    </FormActions>
                )}
            </FormCard>
        </>
    );
}

PaymentReceiptsCreateEdit.layout = ({ receipt }: Props) => ({
    breadcrumbs: [
        { title: 'سندات القبض', href: index() },
        receipt
            ? { title: 'تعديل', href: createEdit(receipt.id) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
