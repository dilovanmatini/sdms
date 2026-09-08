import { Button, Label, TextInput } from 'flowbite-react';
import { Plus, Trash2 } from 'lucide-react';
import {
    AsyncSearchableSelect,
    type SearchableSelectOption,
} from '@/components/async-searchable-select';
import InputError from '@/components/input-error';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { useCurrency, useFormatMoney } from '@/lib/money';
import { openInvoices as openInvoiceLookups } from '@/routes/lookups';

export type ReceiptAllocationDraft = {
    sales_invoice_id: string;
    amount: string;
    grand_total?: string;
    remaining?: string;
};

type Props = {
    allocations: ReceiptAllocationDraft[];
    distributorId: string;
    selectedInvoices?: SearchableSelectOption[];
    invoiceIncludeIds?: string[];
    errors: Record<string, string | undefined>;
    readOnly?: boolean;
    onChange: (allocations: ReceiptAllocationDraft[]) => void;
};

export function ReceiptAllocationsEditor({
    allocations,
    distributorId,
    selectedInvoices = [],
    invoiceIncludeIds = [],
    errors,
    readOnly = false,
    onChange,
}: Props) {
    const formatMoney = useFormatMoney();
    const { symbol } = useCurrency();
    const updateAllocation = (
        index: number,
        field: keyof ReceiptAllocationDraft,
        value: string,
    ) => {
        onChange(
            allocations.map((allocation, i) =>
                i === index ? { ...allocation, [field]: value } : allocation,
            ),
        );
    };

    const addAllocation = () => {
        onChange([...allocations, { sales_invoice_id: '', amount: '' }]);
    };

    const removeAllocation = (index: number) => {
        if (allocations.length === 1) {
            return;
        }

        onChange(allocations.filter((_, i) => i !== index));
    };

    const total = allocations.reduce(
        (sum, allocation) => sum + (Number(allocation.amount) || 0),
        0,
    );

    const hasDistributor = distributorId !== '';

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between gap-3">
                <Label>توزيع المبالغ على الفواتير</Label>
                {!readOnly && (
                    <Button
                        type="button"
                        size="xs"
                        color="light"
                        onClick={addAllocation}
                        disabled={!hasDistributor}
                    >
                        <Plus className="me-1 h-3.5 w-3.5" />
                        إضافة توزيع
                    </Button>
                )}
            </div>
            <InputError message={errors.allocations} />

            {!hasDistributor ? (
                <p className="text-sm text-gray-500">
                    اختر الموزع أولاً لعرض فواتيره المفتوحة.
                </p>
            ) : (
                <div className="space-y-3">
                    {allocations.map((allocation, index) => {
                        const hasInvoiceMeta =
                            allocation.grand_total !== undefined ||
                            allocation.remaining !== undefined;

                        return (
                            <div
                                key={`${distributorId}-${index}`}
                                className="space-y-2 rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                            >
                                <div className="grid items-start gap-3 sm:grid-cols-[minmax(0,1fr)_10rem_auto]">
                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor={`allocations_${index}_sales_invoice_id`}
                                            className="leading-5"
                                        >
                                            الفاتورة
                                        </Label>
                                        <AsyncSearchableSelect
                                            id={`allocations_${index}_sales_invoice_id`}
                                            name={`allocations[${index}][sales_invoice_id]`}
                                            value={allocation.sales_invoice_id}
                                            disabled={readOnly}
                                            required
                                            placeholder="اختر الفاتورة"
                                            searchPlaceholder="ابحث برقم الفاتورة..."
                                            emptyMessage="لا توجد فواتير مفتوحة لهذا الموزع"
                                            initialOptions={selectedInvoices}
                                            buildUrl={(search) =>
                                                openInvoiceLookups.url(
                                                    lookupQuery(search, {
                                                        distributor_id:
                                                            distributorId,
                                                        include:
                                                            invoiceIncludeIds,
                                                    }),
                                                )
                                            }
                                            onChange={(value, option) => {
                                                const nextRemaining =
                                                    option?.meta?.remaining;
                                                const nextGrandTotal =
                                                    option?.meta?.grand_total;

                                                onChange(
                                                    allocations.map(
                                                        (row, i) =>
                                                            i === index
                                                                ? {
                                                                      sales_invoice_id:
                                                                          value,
                                                                      amount:
                                                                          nextRemaining !==
                                                                              undefined &&
                                                                          nextRemaining !==
                                                                              null
                                                                              ? String(
                                                                                    nextRemaining,
                                                                                )
                                                                              : row.amount,
                                                                      remaining:
                                                                          nextRemaining !==
                                                                              undefined &&
                                                                          nextRemaining !==
                                                                              null
                                                                              ? String(
                                                                                    nextRemaining,
                                                                                )
                                                                              : row.remaining,
                                                                      grand_total:
                                                                          nextGrandTotal !==
                                                                              undefined &&
                                                                          nextGrandTotal !==
                                                                              null
                                                                              ? String(
                                                                                    nextGrandTotal,
                                                                                )
                                                                              : row.grand_total,
                                                                  }
                                                                : row,
                                                    ),
                                                );
                                            }}
                                        />
                                        <InputError
                                            message={
                                                errors[
                                                    `allocations.${index}.sales_invoice_id`
                                                ]
                                            }
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor={`allocations_${index}_amount`}
                                            className="leading-5 whitespace-nowrap"
                                        >
                                            المبلغ ({symbol})
                                        </Label>
                                        <TextInput
                                            id={`allocations_${index}_amount`}
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            value={allocation.amount}
                                            disabled={readOnly}
                                            required
                                            onChange={(event) =>
                                                updateAllocation(
                                                    index,
                                                    'amount',
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={
                                                errors[
                                                    `allocations.${index}.amount`
                                                ]
                                            }
                                        />
                                    </div>

                                    {!readOnly && (
                                        <div className="grid gap-2">
                                            <Label
                                                className="invisible leading-5 select-none"
                                                aria-hidden="true"
                                            >
                                                &nbsp;
                                            </Label>
                                            <div className="flex min-h-10.5 items-center">
                                                <Button
                                                    type="button"
                                                    color="red"
                                                    size="xs"
                                                    disabled={
                                                        allocations.length === 1
                                                    }
                                                    onClick={() =>
                                                        removeAllocation(index)
                                                    }
                                                    title="حذف التوزيع"
                                                    aria-label="حذف التوزيع"
                                                    className="inline-flex items-center justify-center p-2"
                                                >
                                                    <Trash2 className="h-3.5 w-3.5" />
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {hasInvoiceMeta && (
                                    <p className="text-xs text-gray-500">
                                        {allocation.grand_total !==
                                            undefined &&
                                            `إجمالي الفاتورة ${allocation.grand_total}`}
                                        {allocation.grand_total !==
                                            undefined &&
                                            allocation.remaining !==
                                                undefined &&
                                            ' · '}
                                        {allocation.remaining !== undefined &&
                                            `المتبقي ${formatMoney(allocation.remaining)}`}
                                    </p>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}

            <div className="text-sm font-medium tabular-nums">
                إجمالي السند: {formatMoney(total.toFixed(2))}
            </div>
        </div>
    );
}
