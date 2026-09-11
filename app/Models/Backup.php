<?php

namespace App\Models;

use App\Enums\BackupStatus;
use Database\Factories\BackupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property BackupStatus $status
 * @property string $disk
 * @property string|null $path
 * @property string|null $filename
 * @property int|null $size
 * @property string|null $error_message
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['status', 'disk', 'path', 'filename', 'size', 'error_message', 'created_by'])]
class Backup extends Model
{
    /** @use HasFactory<BackupFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BackupStatus::class,
            'size' => 'integer',
        ];
    }

    /**
     * @param  Builder<Backup>  $query
     * @return Builder<Backup>
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereIn('status', [
            BackupStatus::Pending,
            BackupStatus::Processing,
        ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canBeDownloaded(): bool
    {
        return $this->status === BackupStatus::Completed
            && is_string($this->path)
            && $this->path !== '';
    }

    public function canBeDeleted(): bool
    {
        return in_array($this->status, [BackupStatus::Completed, BackupStatus::Failed], true);
    }

    public function deleteStoredFile(): void
    {
        if ($this->path === null || $this->path === '') {
            return;
        }

        Storage::disk($this->disk)->delete($this->path);
    }
}
