import { Badge } from 'flowbite-react';

type Props = {
    active: boolean;
};

export function ActiveBadge({ active }: Props) {
    return (
        <Badge color={active ? 'success' : 'gray'} className="w-fit">
            {active ? 'نشط' : 'غير نشط'}
        </Badge>
    );
}
