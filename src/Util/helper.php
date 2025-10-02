<?php

namespace App\Util;

function human_bytes(int $bytes, int $base = 1024, int $decimals = 2, bool $iec = true): string {
    if ($bytes === 0) return '0 o';
    $units = $iec ? ['o','KiB','MiB','GiB','TiB'] : ['o','kB','MB','GB','TB'];
    $i = (int) floor(log($bytes, $base));
    $val = $bytes / ($base ** $i);
    return number_format($val, $decimals, ',', ' ') . ' ' . $units[$i];
}
