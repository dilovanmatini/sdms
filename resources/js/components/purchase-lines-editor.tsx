import { Button, Label, Select, TextInput } from 'flowbite-react';
import { Plus, Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';

export type PurchaseLineDraft = {
    product_id: string;
    quantity: string;
};

type ProductOption = {
    id: number;
    code: string;
    name_ar: string;
};

type Props = {
    lines: PurchaseLineDraft[];
    products: ProductOption[];
    errors: Record<string, string | undefined>;
    readOnly?: boolean;
    onChange: (lines: PurchaseLineDraft[]) => void;
};

export function PurchaseLinesEditor({
    lines,
    products,
    errors,
    readOnly = false,
    onChange,
}: Props) {
    const updateLine = (
        index: number,
        field: keyof PurchaseLineDraft,
        value: string,
    ) => {
        onChange(
            lines.map((line, i) =>
                i === index ? { ...line, [field]: value } : line,
            ),
        );
    };

    const addLine = () => {
        onChange([...lines, { product_id: '', quantity: '' }]);
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
                        className="grid gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-[1fr_8rem_auto] dark:border-gray-700"
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
