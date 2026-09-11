<?php

namespace App\Actions\Backup;

use App\Enums\BackupStatus;
use App\Jobs\GenerateDatabaseBackup;
use App\Models\Backup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StoreAction
{
    public function handle(Request $request): RedirectResponse
    {
        $backup = DB::transaction(function () use ($request): ?Backup {
            if (Backup::query()->inProgress()->lockForUpdate()->exists()) {
                return null;
            }

            return Backup::query()->create([
                'status' => BackupStatus::Pending,
                'disk' => (string) config('backup.disk'),
                'created_by' => $request->user()?->id,
            ]);
        });

        if ($backup === null) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'هناك نسخة احتياطية قيد الإنشاء حالياً.',
            ]);

            return to_route('settings.backups.index');
        }

        GenerateDatabaseBackup::dispatch($backup);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'بدأ إنشاء النسخة الاحتياطية. ستظهر في القائمة عند اكتمالها.',
        ]);

        return to_route('settings.backups.index');
    }
}
