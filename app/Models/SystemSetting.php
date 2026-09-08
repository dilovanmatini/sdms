<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'app_name',
    'currency',
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
                'currency' => Currency::Usd,
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

    /**
     * Logo for print/UI surfaces: uploaded logo, otherwise the bundled fallback.
     */
    public function displayLogoUrl(): string
    {
        return $this->logo_url ?? '/images/sdsm-logo.png';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency' => Currency::class,
        ];
    }
}
