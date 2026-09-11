<?php

namespace App\Actions\Backup;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadAction
{
    public function handle(Backup $backup): StreamedResponse
    {
        abort_unless($backup->canBeDownloaded(), 404);

        $directory = trim((string) config('backup.directory'), '/');
        $path = $backup->path;

        abort_unless(
            is_string($path)
            && $path !== ''
            && ! str_contains($path, '..')
            && dirname($path) === $directory
            && Storage::disk($backup->disk)->exists($path),
            404,
        );

        return Storage::disk($backup->disk)->download(
            $path,
            $backup->filename ?? basename($path),
        );
    }
}
