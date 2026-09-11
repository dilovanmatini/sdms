import { Head, useForm } from '@inertiajs/react';
import { Button, Label, Select, Textarea, TextInput } from 'flowbite-react';
import { Edit, Printer, Wallet } from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo, useState } from 'react';
import PaymentReceiptController from '@/actions/App/Http/Controllers/PaymentReceiptController';
import { AsyncSearchableSelect } from '@/components/async-searchable-select';
import type { SearchableSelectOption } from '@/components/async-searchable-select';
import { ConfirmActionButton } from '@/components/confirm-action-button';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { useCurrency, useFormatMoney } from '@/lib/money';
import { distributors as distributorLookups } from '@/routes/lookups';
import {
    createEdit,
    index,
    print as paymentReceiptsPrint,
} from '@/routes/payment-receipts';

type Props = {
    receipt: {
        id: number;
        number: string;
        receipt_date: string | null;
        distributor_id: number;
        payment_method: string;
        amount: string;
        notes: string | null;
        status: 'draft' | 'posted' | 'cancelled';
        status_label: string;
        is_posted: boolean;
        balance_before: string;
        balance_after: string;
    } | null;
    selected_distributor: SearchableSelectOption | null;
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
    amount: string;
    notes: string;
};

function optionBalance(option: SearchableSelectOption | null): string | null {
    const balance = option?.meta?.balance;

    if (balance === undefined || balance === null || balance === '') {
        return null;
    }

    return String(balance);
}

export default function PaymentReceiptsCreateEdit({
    receipt,
    selected_distributor,
    payment_methods,
    can_edit,
    can_post,
    can_cancel,
    can_print,
}: Props) {
    const formatMoney = useFormatMoney();
    const { symbol } = useCurrency();
    const [outstanding, setOutstanding] = useState<string | null>(
        optionBalance(selected_distributor),
    );
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
        amount: receipt?.amount ?? '',
        notes: receipt?.notes ?? '',
    });

    const remainingAfter = useMemo(() => {
        if (outstanding === null) {
            return null;
        }

        const paid = Number(form.data.amount);

        if (Number.isNaN(paid) || form.data.amount === '') {
            return outstanding;
        }

        return (Number(outstanding) - paid).toFixed(2);
    }, [outstanding, form.data.amount]);

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
                        : 'إنشاء مسودة سند قبض بمبلغ على حساب الموزع'
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
                                onChange={(value, option) => {
                                    form.setData('distributor_id', value);
                                    setOutstanding(
                                        optionBalance(option ?? null),
                                    );
                                }}
                            />
                            <InputError message={form.errors.distributor_id} />
                        </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
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
                                    <option
                                        key={method.value}
                                        value={method.value}
                                    >
                                        {method.label}
                                    </option>
                                ))}
                            </Select>
                            <InputError message={form.errors.payment_method} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="amount">المبلغ ({symbol})</Label>
                            <TextInput
                                id="amount"
                                type="number"
                                min="0.01"
                                step="0.01"
                                value={form.data.amount}
                                disabled={!can_edit}
                                required
                                onChange={(event) =>
                                    form.setData('amount', event.target.value)
                                }
                            />
                            <InputError message={form.errors.amount} />
                        </div>
                    </div>

                    {receipt?.is_posted ? (
                        <div className="grid gap-3 rounded-lg border border-gray-200 p-4 text-sm sm:grid-cols-3 dark:border-gray-700">
                            <div className="grid gap-1">
                                <span className="text-gray-500">
                                    المبلغ السابق
                                </span>
                                <span className="font-medium tabular-nums">
                                    {receipt.balance_before}
                                </span>
                            </div>
                            <div className="grid gap-1">
                                <span className="text-gray-500">
                                    مبلغ السند
                                </span>
                                <span className="font-medium tabular-nums">
                                    {formatMoney(receipt.amount)}
                                </span>
                            </div>
                            <div className="grid gap-1">
                                <span className="text-gray-500">
                                    المبلغ المتبقي
                                </span>
                                <span className="font-medium tabular-nums">
                                    {receipt.balance_after}
                                </span>
                            </div>
                        </div>
                    ) : (
                        form.data.distributor_id !== '' &&
                        outstanding !== null && (
                            <div className="grid gap-3 rounded-lg border border-gray-200 p-4 text-sm sm:grid-cols-3 dark:border-gray-700">
                                <div className="grid gap-1">
                                    <span className="text-gray-500">
                                        المبلغ السابق
                                    </span>
                                    <span className="font-medium tabular-nums">
                                        {formatMoney(outstanding)}
                                    </span>
                                </div>
                                <div className="grid gap-1">
                                    <span className="text-gray-500">
                                        مبلغ السند
                                    </span>
                                    <span className="font-medium tabular-nums">
                                        {form.data.amount === ''
                                            ? '—'
                                            : formatMoney(form.data.amount)}
                                    </span>
                                </div>
                                <div className="grid gap-1">
                                    <span className="text-gray-500">
                                        المبلغ المتبقي
                                    </span>
                                    <span className="font-medium tabular-nums">
                                        {remainingAfter === null
                                            ? '—'
                                            : formatMoney(remainingAfter)}
                                    </span>
                                </div>
                            </div>
                        )
                    )}

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
