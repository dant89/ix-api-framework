<?php

namespace App\Security\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Security\Repository\ApiKeyRepository")
 * @ORM\Table(name="api_key",indexes={@ORM\Index(name="api_key_idx", columns={"api_key"})})
 */
class ApiKey
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected string $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected string $apiKey;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected string $secret;

    /**
     * @ORM\Column(type="string", length=36)
     */
    protected string $companyId;

    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTime $createdAt;

    /**
     * @var Collection|Permission[]
     * @ORM\ManyToMany(targetEntity="Permission")
     */
    private $permissions;

    /**
     * @ORM\OneToMany(targetEntity="App\Security\Entity\RefreshToken", mappedBy="api_key")
     */
    private $refreshTokens;

    public static function create(
        int $id,
        string $name,
        string $apiKey,
        string $secret,
        string $companyId,
        DateTime $createdAt
    ): ApiKey {
        $key = new self();
        $key->setId($id)
            ->setName($name)
            ->setApiKey($apiKey)
            ->setSecret($secret)
            ->setCompanyId($companyId)
            ->setCreatedAt($createdAt);

        return $key;
    }

    public function __construct()
    {
        $this->refreshTokens = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): ApiKey
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ApiKey
    {
        $this->name = $name;
        return $this;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): ApiKey
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): ApiKey
    {
        $this->secret = $secret;
        return $this;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    public function setCompanyId(string $companyId): ApiKey
    {
        $this->companyId = $companyId;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): ApiKey
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @param Permission[] $permissions
     */
    public function setPermissions(array $permissions): ApiKey
    {
        $this->permissions = new ArrayCollection();
        foreach ($permissions as $permission) {
            if (!$this->permissions->contains($permission)) {
                $this->permissions[] = $permission;
            }
        }

        return $this;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions->toArray();
    }

    /**
     * @return Collection|RefreshToken[]
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): self
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens[] = $refreshToken;
            $refreshToken->setApiKey($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): self
    {
        if ($this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->removeElement($refreshToken);
            // set the consuming side to null (unless already changed)
            if ($refreshToken->getApiKey() === $this) {
                $refreshToken->setApiKey(null);
            }
        }

        return $this;
    }

    public function __debugInfo(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->apiKey
        ];
    }
}
