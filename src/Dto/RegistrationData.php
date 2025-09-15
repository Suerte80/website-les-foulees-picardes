<?php

/*
 * Une DTO ( Data Transfert Object ou Objet de transfert de données ) est une classe qui fait le lien ( d'un formulaire
 * -> service -> entités )
 *
 * Grosso modo ici, elle va être utile pour être mappé et transferer les données vers deux classes différentes
 * Member et MembershipRequest.
 *
 * Dans mes propres termes faire le lien entre un formulaire et deux entités différents en utilisant le système d'irrigation de symfony.
 */

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegistrationData
{
    #[
        Assert\Email(
            message: 'Votre email n\'est pas valide.',
        ),
        Assert\Length(
            min: 4,
            max: 255,
            minMessage: 'Votre adresse email doit avoir au moins {{ limit }} caractères.',
            maxMessage: 'Votre adresse email doit avoir au maximum {{ limit }} caractères.'
        )
    ]
    public ?string $email = null;

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
    public ?string $password = null;

    #[
        Assert\NotBlank(
            message: 'Votre prénom ne peux pas être vide.',
        ),
        Assert\Length(
            max: 100,
            maxMessage: 'Votre prénom ne doit pas dépasser {{ limit }}.'
        ),
        Assert\Regex(
            pattern: '/^[\p{L}][\p{L}\s\-\']*$/u',
            message: 'Votre prénom n\'est pas valide.'
        )
    ]
    public ?string $firstname = null;

    #[
        Assert\NotBlank(
            message: 'Votre nom ne peux pas être vide.',
        ),
        Assert\Length(
            max: 100,
            maxMessage: "Votre nom ne doit pas dépasser {{ limit }}."
        ),
        Assert\Regex(
            pattern: '/^[\p{L}][\p{L}\s\-\']*$/u',
            message: 'Votre nom n\'est pas valide.'
        )
    ]
    public ?string $lastname = null;

    #[
        Assert\NotBlank(
            message: 'Votre adresse ne doit pas être vide.'
        ),
        Assert\Length(
            max: 255,
            maxMessage: 'L\'adresse est trop longue.'
        ),
        Assert\Regex(
            pattern: '/^[0-9\p{L}\s\.\,\-\'\/]+$/u',
            message: 'L’adresse ne peut contenir que des lettres, chiffres et ponctuation simple.'
        )
    ]
    public ?string $address = null;

    #[
        Assert\NotBlank(
            message: 'Date de naissance obligatoire.'
        ),
        Assert\LessThan('today', message: 'La date de naissance doit être dans le passé.')
    ]
    public ?\DateTimeInterface $dateOfBirth = null;

    #[
        Assert\NotBlank(
            message: 'Numéro de téléphone obligatoire.'
        ),
        Assert\Length(
            min: 8,
            max: 18,
            minMessage: 'Le numéro de téléphone doit avoir au moins 8 caractères.',
            maxMessage: 'Le numéro de téléphone ne peut pas dépasser {{ limit }}.'
        ),
        Assert\Regex(
            pattern: '/^\+?[0-9 \.]{10,12}/u',
            message: 'Le numéro de téléphone doit être de la forme \'XX.XX.XX.XX.XX\' ou \'XX XX XX XX XX\''
        )
    ]
    public ?string $phone = null;

    public ?string $message = null;

    #[Assert\IsTrue(
        message: 'Vous devez accepté le RGPD.'
    )]
    public ?bool  $rgpdAccepted = false;
}
