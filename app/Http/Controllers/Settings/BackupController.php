<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Backup\DestroyAction;
use App\Actions\Backup\DownloadAction;
use App\Actions\Backup\IndexAction;
use App\Actions\Backup\StoreAction;
use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $this->authorize('viewAny', Backup::class);

        return $action->handle($request);
    }

    public function store(Request $request, StoreAction $action): RedirectResponse
    {
        $this->authorize('create', Backup::class);

        return $action->handle($request);
    }

    public function download(Backup $backup, DownloadAction $action): StreamedResponse
    {
        $this->authorize('download', $backup);

        return $action->handle($backup);
    }

    public function destroy(Backup $backup, DestroyAction $action): RedirectResponse
    {
        $this->authorize('delete', $backup);

        return $action->handle($backup);
    }
}
