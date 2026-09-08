import { Button, Label, TextInput } from 'flowbite-react';
import { Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AsyncSearchableSelect } from '@/components/async-searchable-select';
import type { SearchableSelectOption } from '@/components/async-searchable-select';
import InputError from '@/components/input-error';
import { lookupQuery } from '@/hooks/use-lookup-options';
import { products as productLookups } from '@/routes/lookups';

export type PurchaseLineDraft = {
    product_id: string;
    quantity: string;
};

type Props = {
    lines: PurchaseLineDraft[];
    selectedProducts?: SearchableSelectOption[];
    errors: Record<string, string | undefined>;
    readOnly?: boolean;
    onChange: (lines: PurchaseLineDraft[]) => void;
};

const PRODUCT_PRELOAD_LIMIT = 10;

export function PurchaseLinesEditor({
    lines,
    selectedProducts = [],
    errors,
    readOnly = false,
    onChange,
}: Props) {
    const [pickedProductOptions, setPickedProductOptions] = useState<
        Record<string, SearchableSelectOption>
    >({});

    const selectedProductOptions = useMemo(() => {
        const merged: Record<string, SearchableSelectOption> = {
            ...pickedProductOptions,
        };

        for (const option of selectedProducts) {
            merged[String(option.value)] = option;
        }

        return merged;
    }, [pickedProductOptions, selectedProducts]);

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
                <Label>العناصر</Label>
                {!readOnly && (
                    <Button
                        type="button"
                        size="xs"
                        color="light"
                        onClick={addLine}
                    >
                        <Plus className="me-1 h-3.5 w-3.5" />
                        إضافة عنصر
                    </Button>
                )}
            </div>
            <InputError message={errors.lines} />

            <div className="space-y-3">
                {lines.map((line, index) => {
                    const selectedOption = line.product_id
                        ? selectedProductOptions[line.product_id]
                        : undefined;

                    return (
                        <div
                            key={index}
                            className="grid gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-[1fr_8rem_auto] dark:border-gray-700"
                        >
                            <div className="grid gap-2">
                                <Label htmlFor={`lines_${index}_product_id`}>
                                    المنتج
                                </Label>
                                <AsyncSearchableSelect
                                    id={`lines_${index}_product_id`}
                                    name={`lines[${index}][product_id]`}
                                    value={line.product_id}
                                    disabled={readOnly}
                                    required
                                    placeholder="اختر المنتج"
                                    searchPlaceholder="ابحث عن منتج..."
                                    initialOptions={
                                        selectedOption ? [selectedOption] : []
                                    }
                                    buildUrl={(search) =>
                                        productLookups.url(
                                            lookupQuery(search, {
                                                include:
                                                    line.product_id ||
                                                    undefined,
                                                limit: PRODUCT_PRELOAD_LIMIT,
                                            }),
                                        )
                                    }
                                    onChange={(value, option) => {
                                        if (option) {
                                            setPickedProductOptions(
                                                (current) => ({
                                                    ...current,
                                                    [value]: option,
                                                }),
                                            );
                                        }

                                        updateLine(index, 'product_id', value);
                                    }}
                                />
                                <InputError
                                    message={
                                        errors[`lines.${index}.product_id`]
                                    }
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor={`lines_${index}_quantity`}>
                                    الكمية
                                </Label>
                                <TextInput
                                    id={`lines_${index}_quantity`}
                                    type="number"
                                    min="0"
                                    step="1"
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
                                <div className="grid gap-2">
                                    <Label
                                        className="invisible select-none"
                                        aria-hidden="true"
                                    >
                                        &nbsp;
                                    </Label>
                                    <div className="flex min-h-[2.625rem] items-center">
                                        <Button
                                            type="button"
                                            color="red"
                                            size="xs"
                                            disabled={lines.length === 1}
                                            onClick={() => removeLine(index)}
                                            title="حذف العنصر"
                                            aria-label="حذف العنصر"
                                            className="inline-flex items-center justify-center p-2"
                                        >
                                            <Trash2 className="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
