import { Button, Label, Select, TextInput } from 'flowbite-react';
import { Plus, Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';

export type SalesInvoiceLineDraft = {
    product_id: string;
    quantity: string;
    unit_price: string;
};

type ProductOption = {
    id: number;
    code: string;
    name_ar: string;
};

type Props = {
    lines: SalesInvoiceLineDraft[];
    products: ProductOption[];
    errors: Record<string, string | undefined>;
    readOnly?: boolean;
    onChange: (lines: SalesInvoiceLineDraft[]) => void;
};

function lineTotal(line: SalesInvoiceLineDraft): string {
    const quantity = Number(line.quantity);
    const unitPrice = Number(line.unit_price);

    if (!Number.isFinite(quantity) || !Number.isFinite(unitPrice)) {
        return '0.00';
    }

    return (quantity * unitPrice).toFixed(2);
}

export function SalesInvoiceLinesEditor({
    lines,
    products,
    errors,
    readOnly = false,
    onChange,
}: Props) {
    const updateLine = (
        index: number,
        field: keyof SalesInvoiceLineDraft,
        value: string,
    ) => {
        onChange(
            lines.map((line, i) =>
                i === index ? { ...line, [field]: value } : line,
            ),
        );
    };

    const addLine = () => {
        onChange([
            ...lines,
            { product_id: '', quantity: '', unit_price: '' },
        ]);
    };

    const removeLine = (index: number) => {
        if (lines.length === 1) {
            return;
        }

        onChange(lines.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <Label>البنود</Label>
                {!readOnly && (
                    <Button type="button" size="xs" color="light" onClick={addLine}>
                        <Plus className="me-1 h-3.5 w-3.5" />
                        إضافة بند
                    </Button>
                )}
            </div>
            <InputError message={errors.lines} />

            <div className="space-y-3">
                {lines.map((line, index) => (
                    <div
                        key={index}
                        className="grid gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-[1fr_7rem_7rem_7rem_auto] dark:border-gray-700"
                    >
                        <div className="grid gap-2">
                            <Label htmlFor={`lines_${index}_product_id`}>
                                المنتج
                            </Label>
                            <Select
                                id={`lines_${index}_product_id`}
                                value={line.product_id}
                                disabled={readOnly}
                                required
                                onChange={(event) =>
                                    updateLine(
                                        index,
                                        'product_id',
                                        event.target.value,
                                    )
                                }
                            >
                                <option value="">اختر المنتج</option>
                                {products.map((product) => (
                                    <option key={product.id} value={product.id}>
                                        {product.code} — {product.name_ar}
                                    </option>
                                ))}
                            </Select>
                            <InputError
                                message={errors[`lines.${index}.product_id`]}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`lines_${index}_quantity`}>
                                الكمية
                            </Label>
                            <TextInput
                                id={`lines_${index}_quantity`}
                                type="number"
                                min="0.001"
                                step="any"
                                value={line.quantity}
                                disabled={readOnly}
                                required
                                onChange={(event) =>
                                    updateLine(
                                        index,
                                        'quantity',
                                        event.target.value,
                                    )
                                }
                            />
                            <InputError
                                message={errors[`lines.${index}.quantity`]}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`lines_${index}_unit_price`}>
                                سعر الوحدة
                            </Label>
                            <TextInput
                                id={`lines_${index}_unit_price`}
                                type="number"
                                min="0"
                                step="0.01"
                                value={line.unit_price}
                                disabled={readOnly}
                                required
                                onChange={(event) =>
                                    updateLine(
                                        index,
                                        'unit_price',
                                        event.target.value,
                                    )
                                }
                            />
                            <InputError
                                message={errors[`lines.${index}.unit_price`]}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label>الإجمالي</Label>
                            <TextInput
                                value={lineTotal(line)}
                                readOnly
                                disabled
                            />
                        </div>

                        {!readOnly && (
                            <div className="flex items-end">
                                <Button
                                    type="button"
                                    color="failure"
                                    size="xs"
                                    disabled={lines.length === 1}
                                    onClick={() => removeLine(index)}
                                    title="حذف البند"
                                >
                                    <Trash2 className="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}

export function calculateInvoiceSubtotal(lines: SalesInvoiceLineDraft[]): number {
    return lines.reduce((sum, line) => sum + Number(lineTotal(line)), 0);
}
