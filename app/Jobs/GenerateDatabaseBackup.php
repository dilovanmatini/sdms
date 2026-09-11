<?php

namespace App\Jobs;

use App\Enums\BackupStatus;
use App\Models\Backup;
use App\Services\DatabaseBackupDumper;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\FailOnTimeout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

#[FailOnTimeout]
class GenerateDatabaseBackup implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public int $uniqueFor = 3600;

    public function __construct(public Backup $backup) {}

    public function uniqueId(): string
    {
        return 'database-backup';
    }

    public function handle(DatabaseBackupDumper $dumper): void
    {
        $disk = (string) config('backup.disk');
        $directory = trim((string) config('backup.directory'), '/');
        $relativePath = $directory.'/'.$this->backup->id.'-'.Str::uuid()->toString().'.zip';

        $this->backup->update([
            'status' => BackupStatus::Processing,
            'disk' => $disk,
        ]);

        Storage::disk($disk)->makeDirectory($directory);

        $absolutePath = Storage::disk($disk)->path($relativePath);

        try {
            $dumper->dumpToZip($absolutePath);

            $size = is_file($absolutePath) ? filesize($absolutePath) : false;

            if ($size === false || $size === 0) {
                throw new RuntimeException('Backup archive was not created.');
            }

            $this->backup->update([
                'status' => BackupStatus::Completed,
                'disk' => $disk,
                'path' => $relativePath,
                'filename' => sprintf('backup-%s.zip', now()->format('Y-m-d-His')),
                'size' => $size,
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($relativePath);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->backup->update([
            'status' => BackupStatus::Failed,
            'error_message' => $exception?->getMessage(),
        ]);

        Log::error('Database backup failed.', [
            'backup_id' => $this->backup->id,
            'message' => $exception?->getMessage(),
        ]);
    }
}
