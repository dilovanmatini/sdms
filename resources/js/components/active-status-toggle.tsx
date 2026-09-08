import { Label, ToggleSwitch } from 'flowbite-react';
import { useState } from 'react';
import InputError from '@/components/input-error';

type Props = {
    defaultChecked?: boolean;
    error?: string;
    name?: string;
};

export function ActiveStatusToggle({
    defaultChecked = true,
    error,
    name = 'is_active',
}: Props) {
    const [checked, setChecked] = useState(defaultChecked);

    return (
        <div className="grid gap-2">
            <Label>الحالة</Label>
            <input type="hidden" name={name} value={checked ? '1' : '0'} />
            <ToggleSwitch
                checked={checked}
                color="default"
                label={checked ? 'نشط' : 'غير نشط'}
                onChange={setChecked}
            />
            <InputError message={error} />
        </div>
    );
}
