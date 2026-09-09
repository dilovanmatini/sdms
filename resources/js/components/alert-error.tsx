import { Alert } from 'flowbite-react';
import { AlertCircle } from 'lucide-react';

export default function AlertError({
    errors,
    title,
}: {
    errors: string[];
    title?: string;
}) {
    return (
        <Alert color="failure" icon={AlertCircle}>
            <span className="font-medium">{title || 'حدث خطأ ما.'}</span>
            <ul className="mt-1.5 list-inside list-disc text-sm">
                {Array.from(new Set(errors)).map((error, index) => (
                    <li key={index}>{error}</li>
                ))}
            </ul>
        </Alert>
    );
}
