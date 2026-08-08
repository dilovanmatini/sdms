<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'app_name',
    'logo_path',
    'invoice_header',
    'invoice_footer',
    'receipt_header',
    'receipt_footer',
])]
class SystemSetting extends Model
{
    public static function current(): self
    {
        return once(function (): self {
            $settings = static::query()->first();

            if ($settings !== null) {
                return $settings;
            }

            return static::query()->create([
                'app_name' => (string) config('app.name'),
            ]);
        });
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->logo_path === null || $this->logo_path === '') {
                return null;
            }

            return Storage::disk('public')->url($this->logo_path);
        });
    }
}
