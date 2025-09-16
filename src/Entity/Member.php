<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\PasswordStrength;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
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
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
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
    private ?string $password = null;

    #[ORM\Column(length: 100)]
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
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
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
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
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
    private ?string $address = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[
        Assert\NotBlank(
            message: 'Date de naissance obligatoire.'
        ),
        Assert\LessThan('today', message: 'La date de naissance doit être dans le passé.')
    ]
    private ?\DateTime $dateOfBirth = null;

    #[ORM\Column(length: 20)]
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
    private ?string $phone = null;

    #[ORM\Column(length: 16, options: ['default' => 'pending'])]
    private ?string $membershipStatus;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $membershipExpiresAt = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarFilename = null;

    /**
     * @var Collection<int, MembershipRequest>
     *
     * La liste des demandes d'adhésion validées par ce membre.
     */
    #[ORM\OneToMany(targetEntity: MembershipRequest::class, mappedBy: 'validatedBy')]
    private Collection $membershipRequests;

    /**
     * @var MembershipRequest|null C'est la demande d'adhésion déposée par le membre.
     */
    #[ORM\OneToOne(mappedBy: 'requester', cascade: ['persist', 'remove'])]
    private ?MembershipRequest $membershipRequest = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'members')]
    private Collection $rolesEntities;

    /**
     * @var Collection<int, FileItem>
     */
    #[ORM\OneToMany(targetEntity: FileItem::class, mappedBy: 'uploadedBy')]
    private Collection $fileItems;

    public function __construct()
    {
        $this->membershipRequests = new ArrayCollection();

        $this->createdAt = new \DateTimeImmutable();

        $this->membershipStatus = 'pending';
        $this->rolesEntities = new ArrayCollection();
        $this->fileItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $codes = array_map(fn(Role $role) => $role->getCode(), $this->rolesEntities->toArray());
        $codes[] = 'ROLE_USER';

        return array_unique($codes);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->rolesEntities->clear();
        foreach ($roles as $role) {
            if($role instanceof Role){
                $this->rolesEntities->add($role);
            }
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTime $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMembershipStatus(): ?string
    {
        return $this->membershipStatus;
    }

    public function setMembershipStatus(string $membershipStatus): static
    {
        $this->membershipStatus = $membershipStatus;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->membershipStatus === 'active';
    }

    public function getMembershipExpiresAt(): ?\DateTime
    {
        return $this->membershipExpiresAt;
    }

    public function setMembershipExpiresAt(?\DateTime $membershipExpiresAt): static
    {
        $this->membershipExpiresAt = $membershipExpiresAt;

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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, MembershipRequest>
     */
    public function getMembershipRequests(): Collection
    {
        return $this->membershipRequests;
    }

    public function addMembershipRequest(MembershipRequest $membershipRequest): static
    {
        if (!$this->membershipRequests->contains($membershipRequest)) {
            $this->membershipRequests->add($membershipRequest);
            $membershipRequest->setValidatedBy($this);
        }

        return $this;
    }

    public function removeMembershipRequest(MembershipRequest $membershipRequest): static
    {
        if ($this->membershipRequests->removeElement($membershipRequest)) {
            // set the owning side to null (unless already changed)
            if ($membershipRequest->getValidatedBy() === $this) {
                $membershipRequest->setValidatedBy(null);
            }
        }

        return $this;
    }

    public function getMembershipRequest(): ?MembershipRequest
    {
        return $this->membershipRequest;
    }

    public function setMembershipRequest(MembershipRequest $membershipRequest): static
    {
        // set the owning side of the relation if necessary
        if ($membershipRequest->getRequester() !== $this) {
            $membershipRequest->setRequester($this);
        }

        $this->membershipRequest = $membershipRequest;

        return $this;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRolesEntities(): Collection
    {
        return $this->rolesEntities;
    }

    public function addRolesEntity(Role $rolesEntity): static
    {
        if (!$this->rolesEntities->contains($rolesEntity)) {
            $this->rolesEntities->add($rolesEntity);
        }

        return $this;
    }

    public function removeRolesEntity(Role $rolesEntity): static
    {
        $this->rolesEntities->removeElement($rolesEntity);

        return $this;
    }

    public function getAvatarFilename(): ?string
    {
        return $this->avatarFilename;
    }

    public function setAvatarFilename(?string $avatarFilename): static
    {
        $this->avatarFilename = $avatarFilename;

        return $this;
    }

    /**
     * @return Collection<int, FileItem>
     */
    public function getFileItems(): Collection
    {
        return $this->fileItems;
    }

    public function addFileItem(FileItem $fileItem): static
    {
        if (!$this->fileItems->contains($fileItem)) {
            $this->fileItems->add($fileItem);
            $fileItem->setUploadedBy($this);
        }

        return $this;
    }

    public function removeFileItem(FileItem $fileItem): static
    {
        if ($this->fileItems->removeElement($fileItem)) {
            // set the owning side to null (unless already changed)
            if ($fileItem->getUploadedBy() === $this) {
                $fileItem->setUploadedBy(null);
            }
        }

        return $this;
    }
}
