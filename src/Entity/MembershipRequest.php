<?php

namespace App\Entity;

use App\Repository\MembershipRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembershipRequestRepository::class)]
class MembershipRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column]
    private ?bool $rgpdAccepted = null;

    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    private ?string $status = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reminderSendAt = null;

    #[ORM\ManyToOne(inversedBy: 'membershipRequests')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Member $validatedBy = null;

    #[ORM\OneToOne(inversedBy: 'membershipRequest', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $requester = null;

    #[ORM\Column(length: 64)]
    private ?string $verificationTokenHash = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isEmailVerified = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailVerifiedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();

        $this->status = 'pending';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isRgpdAccepted(): ?bool
    {
        return $this->rgpdAccepted;
    }

    public function setRgpdAccepted(bool $rgpdAccepted): static
    {
        $this->rgpdAccepted = $rgpdAccepted;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getReminderSendAt(): ?\DateTimeImmutable
    {
        return $this->reminderSendAt;
    }

    public function setReminderSendAt(\DateTimeImmutable $reminderSendAt): static
    {
        $this->reminderSendAt = $reminderSendAt;

        return $this;
    }

    public function getValidatedBy(): ?Member
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?Member $validatedBy): static
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    public function getRequester(): ?Member
    {
        return $this->requester;
    }

    public function setRequester(Member $requester): static
    {
        $this->requester = $requester;

        return $this;
    }

    public function getVerificationTokenHash(): ?string
    {
        return $this->verificationTokenHash;
    }

    public function setVerificationTokenHash(string $verificationTokenHash): static
    {
        $this->verificationTokenHash = $verificationTokenHash;

        return $this;
    }

    public function isEmailVerified(): ?bool
    {
        return $this->isEmailVerified;
    }

    public function setIsEmailVerified(bool $isEmailVerified): static
    {
        $this->isEmailVerified = $isEmailVerified;

        return $this;
    }

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTimeImmutable $emailVerifiedAt): static
    {
        $this->emailVerifiedAt = $emailVerifiedAt;

        return $this;
    }
}
