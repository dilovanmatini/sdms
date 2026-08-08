import { router } from '@inertiajs/react';
import { Label, TextInput } from 'flowbite-react';
import { Search } from 'lucide-react';
import { useState, type FormEvent } from 'react';

type Props = {
    url: string;
    initial?: string;
    placeholder?: string;
};

export function SearchFilter({
    url,
    initial = '',
    placeholder = 'بحث...',
}: Props) {
    const [search, setSearch] = useState(initial);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(
            url,
            { search: search || undefined },
            { preserveState: true, replace: true },
        );
    };

    return (
        <form onSubmit={submit} className="flex max-w-md items-end gap-2">
            <div className="grow">
                <Label htmlFor="search" className="sr-only">
                    بحث
                </Label>
                <TextInput
                    id="search"
                    type="search"
                    icon={Search}
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder={placeholder}
                />
            </div>
        </form>
    );
}
