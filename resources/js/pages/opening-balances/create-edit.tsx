import { Head, useForm } from '@inertiajs/react';
import { Button, Label, Textarea, TextInput } from 'flowbite-react';
import { CircleDollarSign, Edit } from 'lucide-react';
import type { FormEvent } from 'react';
import OpeningBalanceController from '@/actions/App/Http/Controllers/OpeningBalanceController';
import { AsyncSearchableSelect } from '@/components/async-searchable-select';
import type { SearchableSelectOption } from '@/components/async-searchable-select';
import { ConfirmActionButton } from '@/components/confirm-action-button';
import { DocumentStatusBadge } from '@/components/document-status-badge';
import { FormActions, FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { useCurrency } from '@/lib/money';
import { distributors as distributorLookups } from '@/routes/lookups';
import { createEdit, index } from '@/routes/opening-balances';

type Props = {
    opening_balance: {
        id: number;
        number: string;
        entry_date: string | null;
        distributor_id: number;
        amount: string;
        notes: string | null;
        status: 'draft' | 'posted' | 'cancelled';
        status_label: string;
        is_posted: boolean;
    } | null;
    selected_distributor: SearchableSelectOption | null;
    can_edit: boolean;
    can_post: boolean;
    can_cancel: boolean;
};

type OpeningBalanceForm = {
    entry_date: string;
    distributor_id: string;
    amount: string;
    notes: string;
};

export default function OpeningBalancesCreateEdit({
    opening_balance: openingBalance,
    selected_distributor,
    can_edit,
    can_post,
    can_cancel,
}: Props) {
    const { symbol } = useCurrency();
    const form = useForm<OpeningBalanceForm>({
        entry_date:
            openingBalance?.entry_date ??
            new Date().toISOString().slice(0, 10),
        distributor_id: openingBalance
            ? String(openingBalance.distributor_id)
            : selected_distributor
              ? String(selected_distributor.value)
              : '',
        amount: openingBalance?.amount ?? '',
        notes: openingBalance?.notes ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (!can_edit) {
            return;
        }

        form.post(
            OpeningBalanceController.storeUpdate.url(openingBalance?.id),
        );
    };

    const isEdit = openingBalance !== null;

    return (
        <>
            <Head
                title={
                    isEdit
                        ? can_edit
                            ? `تعديل مبلغ غير مسدد ${openingBalance.number}`
                            : `عرض مبلغ غير مسدد ${openingBalance.number}`
                        : 'إضافة مبلغ غير مسدد'
                }
            />
            <FormCard
                title={
                    isEdit
                        ? can_edit
                            ? 'تعديل مبلغ غير مسدد'
                            : 'عرض مبلغ غير مسدد'
                        : 'إضافة مبلغ غير مسدد'
                }
                description={
                    isEdit
                        ? openingBalance.number
                        : 'تسجيل مبلغ ما زال الموزع مديناً به دون إنشاء فاتورة'
                }
                icon={isEdit ? Edit : CircleDollarSign}
                actions={
                    isEdit ? (
                        <DocumentStatusBadge
                            status={openingBalance.status}
                            label={openingBalance.status_label}
                        />
                    ) : undefined
                }
            >
                <form
                    id="opening-balance-form"
                    onSubmit={submit}
                    className="space-y-6"
                >
                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="entry_date">تاريخ المبلغ</Label>
                            <TextInput
                                id="entry_date"
                                type="date"
                                value={form.data.entry_date}
                                disabled={!can_edit}
                                onChange={(event) =>
                                    form.setData(
                                        'entry_date',
                                        event.target.value,
                                    )
                                }
                                required
                            />
                            <InputError message={form.errors.entry_date} />
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
                                onChange={(value) =>
                                    form.setData('distributor_id', value)
                                }
                            />
                            <InputError message={form.errors.distributor_id} />
                        </div>
                    </div>

                    <div className="grid gap-2 md:max-w-sm">
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
                            form="opening-balance-form"
                            disabled={form.processing}
                        >
                            {isEdit ? 'حفظ التعديلات' : 'إنشاء المبلغ'}
                        </Button>
                        {can_post && openingBalance && (
                            <ConfirmActionButton
                                href={OpeningBalanceController.post.url(
                                    openingBalance.id,
                                )}
                                color="green"
                                confirmColor="green"
                                confirmTitle="تأكيد نهائي"
                                confirmMessage="هل تريد تأكيد هذا المبلغ غير المسدد نهائياً؟ لن يمكن تعديله بعد ذلك."
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
                                        href={OpeningBalanceController.cancel.url(
                                            openingBalance.id,
                                        )}
                                        color="red"
                                        confirmColor="red"
                                        confirmTitle="إلغاء المبلغ غير المسدد"
                                        confirmMessage="هل أنت متأكد من إلغاء هذا المبلغ غير المسدد النشط؟ سيتم عكس أثره على الذمم."
                                        confirmLabel="إلغاء المبلغ"
                                        confirmingLabel="جارٍ الإلغاء..."
                                    >
                                        إلغاء المبلغ
                                    </ConfirmActionButton>
                                )}
                                <Button color="light" href={index.url()} as="a">
                                    رجوع
                                </Button>
                            </>
                        }
                    />
                )}
            </FormCard>
        </>
    );
}

OpeningBalancesCreateEdit.layout = ({
    opening_balance: openingBalance,
}: Props) => ({
    breadcrumbs: [
        { title: 'المبالغ غير المسددة', href: index() },
        openingBalance
            ? { title: 'تعديل', href: createEdit(openingBalance.id) }
            : { title: 'إضافة', href: createEdit() },
    ],
});
