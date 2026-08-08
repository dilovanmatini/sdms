<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::creating(function (Model $model): void {
            if (Auth::check()) {
                if (! $model->getAttribute('created_by')) {
                    $model->setAttribute('created_by', Auth::id());
                }

                if (! $model->getAttribute('updated_by')) {
                    $model->setAttribute('updated_by', Auth::id());
                }
            }
        });

        static::updating(function (Model $model): void {
            if (Auth::check()) {
                $model->setAttribute('updated_by', Auth::id());
            }
        });

        static::deleting(function (Model $model): void {
            if (! in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
                return;
            }

            if (Auth::check() && ! $model->isForceDeleting()) {
                $model->setAttribute('deleted_by', Auth::id());
                $model->saveQuietly();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
