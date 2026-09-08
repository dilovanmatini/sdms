import { Head } from '@inertiajs/react';
import { Palette } from 'lucide-react';
import AppearanceTabs from '@/components/appearance-tabs';
import { FormCard } from '@/components/form-card';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="إعدادات المظهر" />

            <FormCard
                title="إعدادات المظهر"
                description="تحديث مظهر حسابك"
                icon={Palette}
            >
                <AppearanceTabs />
            </FormCard>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'إعدادات المظهر',
            href: editAppearance(),
        },
    ],
};
