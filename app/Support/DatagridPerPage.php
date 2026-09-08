<?php

namespace App\Support;

final class DatagridPerPage
{
    /** @var list<int> */
    public const OPTIONS = [5, 10, 20, 30, 50, 100];

    public const DEFAULT = 10;

    public const COOKIE = 'datagrid_per_page';

    public static function isAllowed(int $value): bool
    {
        return in_array($value, self::OPTIONS, true);
    }
}
