import { Form, Head } from '@inertiajs/react';
import { Button, Checkbox, FileInput, Label, Textarea, TextInput } from 'flowbite-react';
import { useState } from 'react';
import GeneralSettingsController from '@/actions/App/Http/Controllers/Settings/GeneralSettingsController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { edit } from '@/routes/settings/general';
import { index as settingsIndex } from '@/routes/settings';

type Settings = {
    app_name: string;
    logo_url: string | null;
    invoice_header: string | null;
    invoice_footer: string | null;
    receipt_header: string | null;
    receipt_footer: string | null;
};

type Props = {
    settings: Settings;
};

export default function GeneralSettings({ settings }: Props) {
    const [previewUrl, setPreviewUrl] = useState<string | null>(
        settings.logo_url,
    );
    const [removeLogo, setRemoveLogo] = useState(false);

    return (
        <>
            <Head title="الإعدادات العامة" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="عام"
                    description="اسم التطبيق والشعار ورؤوس وتذييلات المستندات"
                />

                <Form
                    {...GeneralSettingsController.update.form()}
                    encType="multipart/form-data"
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-8"
                    setDefaultsOnSuccess
                >
                    {({ processing, errors }) => (
                        <>
                            <section className="space-y-4">
                                <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                    الهوية
                                </h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="app_name">اسم التطبيق</Label>
                                    <TextInput
                                        id="app_name"
                                        name="app_name"
                                        required
                                        defaultValue={settings.app_name}
                                    />
                                    <InputError message={errors.app_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="logo">الشعار</Label>
                                    {(previewUrl || settings.logo_url) &&
                                        !removeLogo && (
                                            <img
                                                src={
                                                    previewUrl ??
                                                    settings.logo_url ??
                                                    undefined
                                                }
                                                alt="معاينة الشعار"
                                                className="h-16 w-16 rounded-md object-contain"
                                            />
                                        )}
                                    <FileInput
                                        id="logo"
                                        name="logo"
                                        accept="image/*"
                                        onChange={(event) => {
                                            const file =
                                                event.target.files?.[0] ?? null;

                                            if (file === null) {
                                                setPreviewUrl(settings.logo_url);
                                                return;
                                            }

                                            setRemoveLogo(false);
                                            setPreviewUrl(
                                                URL.createObjectURL(file),
                                            );
                                        }}
                                    />
                                    <InputError message={errors.logo} />

                                    {settings.logo_url !== null && (
                                        <div className="flex items-center gap-2">
                                            <Checkbox
                                                id="remove_logo"
                                                name="remove_logo"
                                                value="1"
                                                checked={removeLogo}
                                                onChange={(event) => {
                                                    setRemoveLogo(
                                                        event.target.checked,
                                                    );

                                                    if (event.target.checked) {
                                                        setPreviewUrl(null);
                                                    } else {
                                                        setPreviewUrl(
                                                            settings.logo_url,
                                                        );
                                                    }
                                                }}
                                            />
                                            <Label
                                                htmlFor="remove_logo"
                                                className="font-normal"
                                            >
                                                إزالة الشعار الحالي
                                            </Label>
                                        </div>
                                    )}
                                    <InputError message={errors.remove_logo} />
                                </div>
                            </section>

                            <section className="space-y-4">
                                <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                    الفاتورة
                                </h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="invoice_header">
                                        رأس الفاتورة
                                    </Label>
                                    <Textarea
                                        id="invoice_header"
                                        name="invoice_header"
                                        rows={4}
                                        defaultValue={
                                            settings.invoice_header ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.invoice_header}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="invoice_footer">
                                        تذييل الفاتورة
                                    </Label>
                                    <Textarea
                                        id="invoice_footer"
                                        name="invoice_footer"
                                        rows={4}
                                        defaultValue={
                                            settings.invoice_footer ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.invoice_footer}
                                    />
                                </div>
                            </section>

                            <section className="space-y-4">
                                <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                    سند القبض
                                </h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="receipt_header">
                                        رأس السند
                                    </Label>
                                    <Textarea
                                        id="receipt_header"
                                        name="receipt_header"
                                        rows={4}
                                        defaultValue={
                                            settings.receipt_header ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.receipt_header}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="receipt_footer">
                                        تذييل السند
                                    </Label>
                                    <Textarea
                                        id="receipt_footer"
                                        name="receipt_footer"
                                        rows={4}
                                        defaultValue={
                                            settings.receipt_footer ?? ''
                                        }
                                    />
                                    <InputError
                                        message={errors.receipt_footer}
                                    />
                                </div>
                            </section>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    حفظ الإعدادات
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

GeneralSettings.layout = {
    breadcrumbs: [
        { title: 'إعدادات النظام', href: settingsIndex() },
        { title: 'عام', href: edit() },
    ],
};
