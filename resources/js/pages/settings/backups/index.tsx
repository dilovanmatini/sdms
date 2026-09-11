import { Form, Head, usePoll } from '@inertiajs/react';
import {
    Badge,
    Button,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeadCell,
    TableRow,
} from 'flowbite-react';
import { DatabaseBackup, Download } from 'lucide-react';
import { useEffect } from 'react';
import BackupController from '@/actions/App/Http/Controllers/Settings/BackupController';
import { DeleteButton } from '@/components/delete-button';
import { FormCard } from '@/components/form-card';
import { PaginationLinks } from '@/components/pagination-links';
import type { Paginated } from '@/components/pagination-links';
import { index as settingsIndex } from '@/routes/settings';
import { download, index } from '@/routes/settings/backups';

type BackupStatus = 'pending' | 'processing' | 'completed' | 'failed';

type BackupRow = {
    id: number;
    filename: string | null;
    status: BackupStatus;
    status_label: string;
    size: number | null;
    size_label: string | null;
    created_at: string | null;
    can_download: boolean;
    can_delete: boolean;
};

type Props = {
    backups: Paginated<BackupRow>;
    has_in_progress: boolean;
};

const statusColor: Record<
    BackupStatus,
    'warning' | 'info' | 'success' | 'failure'
> = {
    pending: 'warning',
    processing: 'info',
    completed: 'success',
    failed: 'failure',
};

export default function BackupsIndex({ backups, has_in_progress }: Props) {
    const { start, stop } = usePoll(
        2000,
        { only: ['backups', 'has_in_progress'] },
        { autoStart: has_in_progress },
    );

    useEffect(() => {
        if (has_in_progress) {
            start();
        } else {
            stop();
        }
    }, [has_in_progress, start, stop]);

    return (
        <>
            <Head title="النسخ الاحتياطي" />
            <FormCard
                title="النسخ الاحتياطي"
                description="إنشاء وتنزيل نسخ احتياطية من قاعدة البيانات"
                icon={DatabaseBackup}
                actions={
                    <Form {...BackupController.store.form()}>
                        {({ processing }) => (
                            <Button
                                type="submit"
                                disabled={processing || has_in_progress}
                                className="inline-flex items-center gap-2"
                            >
                                <DatabaseBackup className="h-4 w-4" />
                                {has_in_progress
                                    ? 'جارٍ الإنشاء...'
                                    : 'إنشاء نسخة احتياطية'}
                            </Button>
                        )}
                    </Form>
                }
            >
                <div className="space-y-4">
                    {has_in_progress && (
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            جارٍ إنشاء نسخة احتياطية. ستُحدَّث القائمة تلقائياً
                            عند اكتمالها.
                        </p>
                    )}

                    <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableHeadCell className="text-start">
                                        الملف
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الحالة
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        الحجم
                                    </TableHeadCell>
                                    <TableHeadCell className="text-start">
                                        التاريخ
                                    </TableHeadCell>
                                    <TableHeadCell className="text-end">
                                        <span className="sr-only">إجراءات</span>
                                    </TableHeadCell>
                                </TableRow>
                            </TableHead>
                            <TableBody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {backups.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-gray-500"
                                        >
                                            لا توجد نسخ احتياطية
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    backups.data.map((backup) => (
                                        <TableRow key={backup.id}>
                                            <TableCell className="text-start font-medium">
                                                {backup.filename ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                <Badge
                                                    color={
                                                        statusColor[
                                                            backup.status
                                                        ]
                                                    }
                                                    className="w-fit"
                                                >
                                                    {backup.status_label}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {backup.size_label ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-start">
                                                {backup.created_at ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-end">
                                                <div className="flex items-center justify-end gap-2">
                                                    {backup.can_download && (
                                                        <Button
                                                            color="light"
                                                            size="xs"
                                                            href={download.url(
                                                                backup.id,
                                                            )}
                                                            as="a"
                                                            title="تنزيل"
                                                            aria-label="تنزيل"
                                                            className="inline-flex items-center justify-center p-2"
                                                        >
                                                            <Download className="h-3.5 w-3.5" />
                                                        </Button>
                                                    )}
                                                    {backup.can_delete && (
                                                        <DeleteButton
                                                            href={BackupController.destroy.url(
                                                                backup.id,
                                                            )}
                                                            confirmTitle="حذف النسخة الاحتياطية"
                                                            confirmMessage="هل أنت متأكد من حذف هذه النسخة الاحتياطية؟ لا يمكن التراجع عن هذا الإجراء."
                                                        />
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    {backups.total > 0 && (
                        <PaginationLinks meta={backups} storageKey="backups" />
                    )}
                </div>
            </FormCard>
        </>
    );
}

BackupsIndex.layout = {
    breadcrumbs: [
        { title: 'إعدادات النظام', href: settingsIndex() },
        { title: 'النسخ الاحتياطي', href: index() },
    ],
};
