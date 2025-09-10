<?php

namespace App\Entity;

use App\Repository\RolePermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolePermissionRepository::class)]
class RolePermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'rolePermissions')]
    private Collection $roleId;

    /**
     * @var Collection<int, Permission>
     */
    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'rolePermissions')]
    private Collection $permissionId;

    public function __construct()
    {
        $this->roleId = new ArrayCollection();
        $this->permissionId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoleId(): Collection
    {
        return $this->roleId;
    }

    public function addRoleId(Role $roleId): static
    {
        if (!$this->roleId->contains($roleId)) {
            $this->roleId->add($roleId);
        }

        return $this;
    }

    public function removeRoleId(Role $roleId): static
    {
        $this->roleId->removeElement($roleId);

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissionId(): Collection
    {
        return $this->permissionId;
    }

    public function addPermissionId(Permission $permissionId): static
    {
        if (!$this->permissionId->contains($permissionId)) {
            $this->permissionId->add($permissionId);
        }

        return $this;
    }

    public function removePermissionId(Permission $permissionId): static
    {
        $this->permissionId->removeElement($permissionId);

        return $this;
    }
}
