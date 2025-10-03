<?php

namespace App\Entity;

use App\Repository\SocialRunRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SocialRunRepository::class)]
class SocialRun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le champt ne doit pas être vide.')]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "Message trop court (minimim: {{ limit }} caractère(s))",
        maxMessage: 'Message trop long (maximum: {{ limit }} caractères)',
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le champt ne doit pas être vide.')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(
        value: 'now',
        message: 'La date doit être dans le futur.',
    )]
    private ?\DateTimeImmutable $startingAt = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le champt ne doit pas être vide.')]
    #[Assert\Length(
        min: 1,
        max: 150,
        minMessage: "Message trop court (minimum: {{ limit }}).",
        maxMessage: 'Message trop long (maximum: {{ limit }}).',
    )]
    private ?string $meetingPoint = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartingAt(): ?\DateTimeImmutable
    {
        return $this->startingAt;
    }

    public function setStartingAt(\DateTimeImmutable $startingAt): static
    {
        $this->startingAt = $startingAt;

        return $this;
    }

    public function getMeetingPoint(): ?string
    {
        return $this->meetingPoint;
    }

    public function setMeetingPoint(string $meetingPoint): static
    {
        $this->meetingPoint = $meetingPoint;

        return $this;
    }
}
