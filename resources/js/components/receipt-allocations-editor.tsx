import { Button, Label, Select, TextInput } from 'flowbite-react';
import { Plus, Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';

export type ReceiptAllocationDraft = {
    sales_invoice_id: string;
    amount: string;
};

export type OpenInvoiceOption = {
    id: number;
    number: string;
    invoice_date: string | null;
    distributor_id: number;
    grand_total: string;
    remaining: string;
};

type Props = {
    allocations: ReceiptAllocationDraft[];
    invoices: OpenInvoiceOption[];
    errors: Record<string, string | undefined>;
    readOnly?: boolean;
    onChange: (allocations: ReceiptAllocationDraft[]) => void;
};

export function ReceiptAllocationsEditor({
    allocations,
    invoices,
    errors,
    readOnly = false,
    onChange,
}: Props) {
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

    const selectedInvoice = (invoiceId: string) =>
        invoices.find((invoice) => String(invoice.id) === invoiceId);

    const total = allocations.reduce(
        (sum, allocation) => sum + (Number(allocation.amount) || 0),
        0,
    );

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <Label>توزيع المبالغ على الفواتير</Label>
                {!readOnly && (
                    <Button
                        type="button"
                        size="xs"
                        color="light"
                        onClick={addAllocation}
                        disabled={invoices.length === 0}
                    >
                        <Plus className="me-1 h-3.5 w-3.5" />
                        إضافة توزيع
                    </Button>
                )}
            </div>
            <InputError message={errors.allocations} />

            {invoices.length === 0 ? (
                <p className="text-sm text-gray-500">
                    لا توجد فواتير مفتوحة لهذا الموزع.
                </p>
            ) : (
                <div className="space-y-3">
                    {allocations.map((allocation, index) => {
                        const invoice = selectedInvoice(
                            allocation.sales_invoice_id,
                        );

                        return (
                            <div
                                key={index}
                                className="grid gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-[1fr_8rem_auto] dark:border-gray-700"
                            >
                                <div className="grid gap-2">
                                    <Label
                                        htmlFor={`allocations_${index}_sales_invoice_id`}
                                    >
                                        الفاتورة
                                    </Label>
                                    <Select
                                        id={`allocations_${index}_sales_invoice_id`}
                                        value={allocation.sales_invoice_id}
                                        disabled={readOnly}
                                        required
                                        onChange={(event) => {
                                            const nextId = event.target.value;
                                            const nextInvoice =
                                                selectedInvoice(nextId);
                                            onChange(
                                                allocations.map((row, i) =>
                                                    i === index
                                                        ? {
                                                              sales_invoice_id:
                                                                  nextId,
                                                              amount:
                                                                  nextInvoice?.remaining ??
                                                                  row.amount,
                                                          }
                                                        : row,
                                                ),
                                            );
                                        }}
                                    >
                                        <option value="">اختر الفاتورة</option>
                                        {invoices.map((option) => (
                                            <option
                                                key={option.id}
                                                value={option.id}
                                            >
                                                {option.number} — متبقي{' '}
                                                {option.remaining}
                                            </option>
                                        ))}
                                    </Select>
                                    <InputError
                                        message={
                                            errors[
                                                `allocations.${index}.sales_invoice_id`
                                            ]
                                        }
                                    />
                                    {invoice && (
                                        <p className="text-xs text-gray-500">
                                            إجمالي الفاتورة {invoice.grand_total}{' '}
                                            · المتبقي {invoice.remaining}
                                        </p>
                                    )}
                                </div>

                                <div className="grid gap-2">
                                    <Label
                                        htmlFor={`allocations_${index}_amount`}
                                    >
                                        المبلغ
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
                                    <div className="flex items-end">
                                        <Button
                                            type="button"
                                            color="failure"
                                            size="xs"
                                            disabled={allocations.length === 1}
                                            onClick={() =>
                                                removeAllocation(index)
                                            }
                                            title="حذف التوزيع"
                                        >
                                            <Trash2 className="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}

            <div className="text-sm font-medium tabular-nums">
                إجمالي السند: {total.toFixed(2)}
            </div>
        </div>
    );
}
