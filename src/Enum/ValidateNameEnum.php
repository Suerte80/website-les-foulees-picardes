<?php

namespace App\Enum;

enum ValidateNameEnum
{
    case SUCCESS;
    case NAME_NOT_VALID;
    case NAME_SIZE_OVER_LIMIT;
    case NAME_CHARACTER_NOT_ALLOWED;

    public function getMessage(): string
    {
        return match ($this) {
            ValidateNameEnum::SUCCESS => 'Ok',
            ValidateNameEnum::NAME_NOT_VALID => 'Le nom doit être valide.',
            ValidateNameEnum::NAME_SIZE_OVER_LIMIT => 'Le nom du fichier doit être inférieur a 255 caractères.',
            ValidateNameEnum::NAME_CHARACTER_NOT_ALLOWED => 'Caractères non autorisés.',
        };
    }

    public function getStatus(): int
    {
        return match ($this) {
            ValidateNameEnum::NAME_NOT_VALID,
            ValidateNameEnum::NAME_SIZE_OVER_LIMIT,
            ValidateNameEnum::NAME_CHARACTER_NOT_ALLOWED => 400,
            default => 200,
        };
    }

    public function getStatusMessage(): string
    {
        return match ($this) {
            ValidateNameEnum::NAME_NOT_VALID,
            ValidateNameEnum::NAME_SIZE_OVER_LIMIT,
            ValidateNameEnum::NAME_CHARACTER_NOT_ALLOWED => 'error',
            default => 'success'
        };
    }
}
