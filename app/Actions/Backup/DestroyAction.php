<?php

namespace App\Actions\Backup;

use App\Models\Backup;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DestroyAction
{
    public function handle(Backup $backup): RedirectResponse
    {
        $backup->deleteStoredFile();
        $backup->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف النسخة الاحتياطية.']);

        return to_route('settings.backups.index');
    }
}
