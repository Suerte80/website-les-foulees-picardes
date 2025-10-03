<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BulkEmailData
{
    #[Assert\NotBlank(message: 'Un expéditeur est requis.')]
    #[Assert\Email(message: 'Adresse expéditeur invalide.')]
    public ?string $from = null;

    #[Assert\NotBlank(message: 'Le sujet est obligatoire.')]
    #[Assert\Length(max: 120, maxMessage: 'Le sujet ne doit pas dépasser {{ limit }} caractères.')]
    public ?string $subject = null;

    #[Assert\NotBlank(message: 'Le contenu du message est obligatoire.')]
    public ?string $body = null;
}
