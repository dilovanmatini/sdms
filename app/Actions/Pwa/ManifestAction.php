<?php

namespace App\Actions\Pwa;

use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;

class ManifestAction
{
    public function handle(): JsonResponse
    {
        $name = SystemSetting::current()->app_name;
        $shortName = mb_strlen($name) > 12 ? mb_substr($name, 0, 12) : $name;

        return response()
            ->json([
                'id' => '/',
                'name' => $name,
                'short_name' => $shortName,
                'description' => $name,
                'start_url' => '/dashboard',
                'scope' => '/',
                'display' => 'standalone',
                'orientation' => 'any',
                'lang' => 'ar',
                'dir' => 'rtl',
                'theme_color' => '#2563eb',
                'background_color' => '#f9fafb',
                'categories' => ['business', 'finance'],
                'icons' => [
                    [
                        'src' => '/icons/icon-64.png',
                        'sizes' => '64x64',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                    [
                        'src' => '/icons/icon-192.png',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                    [
                        'src' => '/icons/icon-512.png',
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ],
                    [
                        'src' => '/icons/icon-512.png',
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'maskable',
                    ],
                ],
            ], 200, [
                'Content-Type' => 'application/manifest+json',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
