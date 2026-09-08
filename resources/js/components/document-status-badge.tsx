import { Badge } from 'flowbite-react';

type Props = {
    status: 'draft' | 'posted' | 'cancelled';
    label: string;
};

const badgeColor = {
    draft: 'warning',
    posted: 'success',
    cancelled: 'failure',
} as const;

export function DocumentStatusBadge({ status, label }: Props) {
    return (
        <Badge color={badgeColor[status]} className="w-fit">
            {label}
        </Badge>
    );
}
