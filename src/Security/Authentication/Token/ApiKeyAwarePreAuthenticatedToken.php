<?php

namespace App\Security\Authentication\Token;

use App\Security\Entity\ApiKey;
use App\Security\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class ApiKeyAwarePreAuthenticatedToken extends PreAuthenticatedToken implements ApiKeyAwareTokenInterface
{
    protected ApiKey $apiKey;

    public function __construct(User $user, string $credentials, string $providerKey, array $roles = [], ApiKey $apiKey = null)
    {
        $this->apiKey = $apiKey;
        parent::__construct($user, $credentials, $providerKey, $roles);
    }

    public function getAuthenticatedApiKey(): ?ApiKey
    {
        return $this->apiKey;
    }
}
