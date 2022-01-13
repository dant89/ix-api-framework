<?php

namespace App\Security\Authentication\Token;

use App\Security\Entity\ApiKey;

interface ApiKeyAwareTokenInterface
{
    public function getAuthenticatedApiKey(): ?ApiKey;
}
