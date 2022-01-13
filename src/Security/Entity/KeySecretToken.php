<?php

namespace App\Security\Entity;

use App\Security\Authentication\Token\ApiKeyAwareTokenInterface;
use function count;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class KeySecretToken extends AbstractToken implements ApiKeyAwareTokenInterface
{
    protected ?array $credentials;
    protected string $providerKey;
    protected ?string $subjectId;
    protected ?ApiKey $authenticatedApiKey;

    public function __construct(
        string $apiKey,
        string $secret,
        string $providerKey,
        ?string $subjectId = null,
        ?User $user = null,
        array $roles = [],
        ?ApiKey $authenticatedApiKey = null
    ) {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new InvalidArgumentException('$providerKey must not be empty.');
        }
        if (!is_null($user)) {
            $this->setUser($user);
        }
        $this->credentials = ['api_key' => $apiKey, 'secret' => $secret];
        $this->providerKey = $providerKey;
        $this->subjectId = $subjectId;
        if ($authenticatedApiKey) {
            $this->authenticatedApiKey = $authenticatedApiKey;
        }

        parent::setAuthenticated(count($roles) > 0);
    }

    public function setSubjectId(?string $subjectId = null): self
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function getSubjectId(): ?string
    {
        return $this->subjectId;
    }

    public function setAuthenticated(bool $isAuthenticated): void
    {
        if ($isAuthenticated) {
            throw new LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false);
    }

    public function getCredentials(): ?array
    {
        return $this->credentials;
    }

    public function getApiKey(): ?string
    {
        return $this->credentials['api_key'];
    }

    public function getSecret(): ?string
    {
        return $this->credentials['secret'];
    }

    public function getProviderKey(): string
    {
        return $this->providerKey;
    }

    public function eraseCredentials(): void
    {
        parent::eraseCredentials();

        $this->credentials = null;
    }

    public function getAuthenticatedApiKey(): ?ApiKey
    {
        return $this->authenticatedApiKey;
    }

    public function setAuthenticatedApiKey(?ApiKey $authenticatedApiKey): KeySecretToken
    {
        $this->authenticatedApiKey = $authenticatedApiKey;
        return $this;
    }
}
