<?php

namespace App\Util;

final class Sanitizer
{
    public static function sanitizeName(string $name): string
    {
        // évite ../, caractères bizarres, et normalise un peu
        $name = str_replace(['..', '\\'], ['.', '/'], $name);
        $name = preg_replace('~[^\p{L}\p{N}_\.\- ]+~u', '_', $name);
        return trim($name) ?: 'item';
    }
}
