import { Badge } from 'flowbite-react';

type Props = {
    status: 'draft' | 'posted';
    label: string;
};

export function DocumentStatusBadge({ status, label }: Props) {
    return (
        <Badge color={status === 'posted' ? 'success' : 'warning'} className="w-fit">
            {label}
        </Badge>
    );
}
