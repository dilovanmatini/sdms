import { usePage } from '@inertiajs/react';
import type { ImgHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

const fallbackLogo = '/images/logo.png';

type Props = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'> & {
    alt?: string;
};

export default function AppLogoIcon({
    className,
    alt = 'SDSM',
    ...props
}: Props) {
    const { logoUrl, name } = usePage().props;

    return (
        <img
            src={logoUrl ?? fallbackLogo}
            alt={alt === 'SDSM' ? name : alt}
            className={cn('rounded-md object-contain', className)}
            {...props}
        />
    );
}
