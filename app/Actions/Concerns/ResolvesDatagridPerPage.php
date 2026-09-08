<?php

namespace App\Actions\Concerns;

use App\Support\DatagridPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

trait ResolvesDatagridPerPage
{
    protected function perPage(Request $request, string $grid): int
    {
        $fromRequest = $request->integer('per_page');

        if (DatagridPerPage::isAllowed($fromRequest)) {
            $this->rememberPerPage($request, $grid, $fromRequest);

            return $fromRequest;
        }

        $stored = $this->storedPerPage($request, $grid);

        if (DatagridPerPage::isAllowed($stored)) {
            return $stored;
        }

        return DatagridPerPage::DEFAULT;
    }

    protected function storedPerPage(Request $request, string $grid): int
    {
        $preferences = $this->perPagePreferences($request);

        return (int) ($preferences[$grid] ?? 0);
    }

    /**
     * @return array<string, int>
     */
    protected function perPagePreferences(Request $request): array
    {
        $cookie = $request->cookie(DatagridPerPage::COOKIE);

        if (! is_string($cookie) || $cookie === '') {
            return [];
        }

        $decoded = json_decode($cookie, true);

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->mapWithKeys(fn (mixed $value, mixed $key): array => [
                (string) $key => (int) $value,
            ])
            ->all();
    }

    protected function rememberPerPage(Request $request, string $grid, int $perPage): void
    {
        $preferences = $this->perPagePreferences($request);
        $preferences[$grid] = $perPage;

        Cookie::queue(
            cookie(
                name: DatagridPerPage::COOKIE,
                value: json_encode($preferences, JSON_THROW_ON_ERROR),
                minutes: 60 * 24 * 365,
                httpOnly: false,
            ),
        );
    }
}
