<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class BytesExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // usage: {{ size|human_bytes() }} ou {{ size|human_bytes(2, 'iec') }}
            new TwigFilter('human_bytes', [$this, 'humanBytes']),
        ];
    }

    /**
     * @param int|float|string|null $bytes
     * @param int                   $decimals  nombre de décimales
     * @param string                $system    'si' => ko/Mo/Go (1000), 'iec' => KiB/MiB (1024)
     */
    public function humanBytes(int|float|string|null $bytes, int $decimals = 2, string $system = 'si'): string
    {
        if ($bytes === null || $bytes === '') return '0 o';
        $bytes = (int) $bytes;
        if ($bytes <= 0) return '0 o';

        $isSi = strtolower($system) === 'si';
        $base  = $isSi ? 1000 : 1024;
        $units = $isSi ? ['o','ko','Mo','Go','To','Po'] : ['o','KiB','MiB','GiB','TiB','PiB'];

        $i = (int) floor(log($bytes, $base));
        $i = max(0, min($i, count($units) - 1));

        $val = $bytes / ($base ** $i);

        // format FR : 1 234,56
        return number_format($val, $decimals, ',', ' ') . ' ' . $units[$i];
    }
}
