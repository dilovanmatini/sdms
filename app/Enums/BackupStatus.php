<?php

namespace App\Enums;

enum BackupStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الانتظار',
            self::Processing => 'جارٍ الإنشاء',
            self::Completed => 'مكتمل',
            self::Failed => 'فشل',
        };
    }

    public function isInProgress(): bool
    {
        return $this === self::Pending || $this === self::Processing;
    }
}
