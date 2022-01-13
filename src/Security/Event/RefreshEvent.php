<?php

namespace App\Security\Event;

use App\Security\Authentication\Token\ApiKeyAwarePreAuthenticatedToken;
use Gesdinet\JWTRefreshTokenBundle\Event\Event;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;

class RefreshEvent extends Event
{
    private $refreshToken;

    private $preAuthenticatedToken;

    public function __construct(RefreshTokenInterface $refreshToken, ApiKeyAwarePreAuthenticatedToken $preAuthenticatedToken)
    {
        $this->refreshToken = $refreshToken;
        $this->preAuthenticatedToken = $preAuthenticatedToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function getPreAuthenticatedToken()
    {
        return $this->preAuthenticatedToken;
    }
}
