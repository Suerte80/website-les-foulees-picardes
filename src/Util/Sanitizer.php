<?php

namespace App\Util;

use App\Enum\ValidateNameEnum;

final class Sanitizer
{
    public static function sanitizeName(string $name): string
    {
        // évite ../, caractères bizarres, et normalise un peu
        $name = str_replace(['..', '\\'], ['.', '/'], $name);
        $name = preg_replace('~[^\p{L}\p{N}_\.\- ]+~u', '_', $name);
        return trim($name) ?: 'item';
    }

    public static function validateName(string $name): ValidateNameEnum
    {
        // Vérification si la chaine est vide.
        if($name === '')
            return ValidateNameEnum::NAME_NOT_VALID;

        // Vérification de la taille de la chaine.
        if(mb_strlen($name) > 255)
            return ValidateNameENum::NAME_SIZE_OVER_LIMIT;

        // Vérification caractère non autorisé.
        if (!preg_match('/^[\p{L}\p{N}\s._-]+$/u', $name))
            return ValidateNameEnum::NAME_CHARACTER_NOT_ALLOWED;

        return ValidateNameEnum::SUCCESS;
    }
}
