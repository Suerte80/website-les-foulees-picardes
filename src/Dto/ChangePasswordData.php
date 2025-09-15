<?php

namespace App\Dto;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

#[Assert\Expression(
    "this.oldPassword != this.newPassword",
    message: 'Notre nouveau mot de passe doit être différent de votre mot de passe actuel.'
)]
class ChangePasswordData
{
    #[
        Assert\NotBlank(
            message: 'Votre mot de passe ne peux pas être vide.',
        ),
        Assert\Length(
            max: 255,
            maxMessage: 'Le nombre de caractère ne doit pas dépasser {{ limit }}.'
        ),
        UserPassword(
            message: 'Le mot de passe est incorrect.',
        )
    ]
    public ?string $oldPassword = null;

    #[
        Assert\NotBlank(
            message: 'Votre mot de passe ne peux pas être vide.',
        ),
        Assert\Length(
            max: 255,
            maxMessage: 'Le nombre de caractère ne doit pas dépasser {{ limit }}.'
        ),
        Assert\PasswordStrength(
            minScore: PasswordStrength::STRENGTH_MEDIUM,
            message: 'Votre mot de passe n\'est pas assez fort.'
        )
    ]
    public ?string $newPassword = null;
}
