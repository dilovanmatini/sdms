import { Head, Link } from '@inertiajs/react';
import { Card } from 'flowbite-react';
import { Ruler, Settings2, UserCog } from 'lucide-react';
import type { ComponentType } from 'react';
import Heading from '@/components/heading';
import { toUrl } from '@/lib/utils';
import { index as settingsIndex } from '@/routes/settings';

type SettingsCard = {
    title: string;
    description: string;
    href: string;
    ability: string | null;
};

type Props = {
    cards: SettingsCard[];
};

const iconByTitle: Record<string, ComponentType<{ className?: string }>> = {
    عام: Settings2,
    'وحدات القياس': Ruler,
    المستخدمون: UserCog,
};

export default function SettingsIndex({ cards }: Props) {
    return (
        <>
            <Head title="إعدادات النظام" />
            <div className="space-y-6">
                <Heading
                    title="إعدادات النظام"
                    description="اختر قسماً لإدارة إعدادات النظام"
                />

                {cards.length === 0 ? (
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        لا توجد أقسام إعدادات متاحة لحسابك.
                    </p>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        {cards.map((card) => {
                            const Icon = iconByTitle[card.title] ?? Ruler;

                            return (
                                <Link
                                    key={card.href}
                                    href={toUrl(card.href)}
                                    className="block rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                                >
                                    <Card className="h-full transition hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <div className="flex items-start gap-3">
                                            <div className="rounded-lg bg-primary-50 p-2 text-primary-700 dark:bg-gray-700 dark:text-primary-300">
                                                <Icon className="h-5 w-5" />
                                            </div>
                                            <div className="space-y-1">
                                                <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {card.title}
                                                </h2>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                                    {card.description}
                                                </p>
                                            </div>
                                        </div>
                                    </Card>
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>
        </>
    );
}

SettingsIndex.layout = {
    breadcrumbs: [{ title: 'إعدادات النظام', href: settingsIndex() }],
};
