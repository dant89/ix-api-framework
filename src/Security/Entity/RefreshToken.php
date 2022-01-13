<?php

namespace App\Security\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Security\Repository\RefreshTokenRepository")
 * @ORM\Table(name="refresh_token",indexes={@ORM\Index(name="refresh_token_idx", columns={"refresh_token"})})
 * @UniqueEntity("refreshToken")
 */
class RefreshToken implements RefreshTokenInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected string $refreshToken;

    /**
     * @ORM\Column(type="string")
     */
    protected string $sub;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    protected DateTime $valid;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Security\Entity\ApiKey", inversedBy="refreshTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $apiKey;

    /**
     * @ORM\ManyToMany(targetEntity="\App\Security\Entity\Permission")
     */
    private $permissions;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setRefreshToken($refreshToken = null): self
    {
        $this->refreshToken = null === $refreshToken
            ? bin2hex(openssl_random_pseudo_bytes(64))
            : $refreshToken;

        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setValid($valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getValid(): DateTime
    {
        return $this->valid;
    }

    public function setSub(string $sub): self
    {
        $this->sub = $sub;
        return $this;
    }

    public function getSub(): string
    {
        return $this->sub;
    }

    public function setUsername($username): self
    {
        $this->setSub($username);
        return $this;
    }

    public function getUsername(): string
    {
        return $this->getSub();
    }

    public function isValid(): bool
    {
        return $this->valid >= new DateTime();
    }

    public function __toString(): string
    {
        return $this->getRefreshToken();
    }

    public function getApiKey(): ?ApiKey
    {
        return $this->apiKey;
    }

    public function setApiKey(?ApiKey $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

      public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * Return permissions asserted by this key in the form of ROLE_* strings
     */
    public function getPermissionStrings(): array
    {
        return array_map(
            function (Permission $p) {
                return $p->getPermission();
            },
            $this->getPermissions()->toArray()
        );
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->contains($permission)) {
            $this->permissions->removeElement($permission);
        }

        return $this;
    }
}
