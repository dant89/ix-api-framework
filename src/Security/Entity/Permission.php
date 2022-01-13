<?php

namespace App\Security\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Security\Repository\PermissionRepository")
 * @ORM\Table(name="permission")
 * @UniqueEntity("permission")
 */
class Permission
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private string $permission;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setPermission($permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }
}
