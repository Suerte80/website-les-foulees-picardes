<?php

namespace App\Util;

final class Helper
{
    public static function humanBytes(int $bytes, int $base = 1024, int $decimals = 2, bool $iec = true): string
    {
        if ($bytes === 0) {
            return '0 o';
        }

        $units = $iec ? ['o', 'KiB', 'MiB', 'GiB', 'TiB'] : ['o', 'kB', 'MB', 'GB', 'TB'];
        $index = (int) floor(log($bytes, $base));
        $index = max(0, min($index, count($units) - 1));
        $value = $bytes / ($base ** $index);

        return number_format($value, $decimals, ',', ' ') . ' ' . $units[$index];
    }
}
