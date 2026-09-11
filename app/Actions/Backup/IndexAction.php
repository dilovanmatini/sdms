<?php

namespace App\Actions\Backup;

use App\Actions\Concerns\ResolvesDatagridPerPage;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Inertia\Inertia;
use Inertia\Response;

class IndexAction
{
    use ResolvesDatagridPerPage;

    public function handle(Request $request): Response
    {
        $backups = Backup::query()
            ->latest('id')
            ->paginate($this->perPage($request, 'backups'))
            ->withQueryString()
            ->through(function (Backup $backup): array {
                $sizeLabel = $backup->size === null ? null : Number::fileSize($backup->size, 1);

                return [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'status' => $backup->status->value,
                    'status_label' => $backup->status->label(),
                    'size' => $backup->size,
                    'size_label' => is_string($sizeLabel) ? $sizeLabel : null,
                    'created_at' => $backup->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                    'can_download' => $backup->canBeDownloaded(),
                    'can_delete' => $backup->canBeDeleted(),
                ];
            });

        return Inertia::render('settings/backups/index', [
            'backups' => $backups,
            'has_in_progress' => Backup::query()->inProgress()->exists(),
        ]);
    }
}
