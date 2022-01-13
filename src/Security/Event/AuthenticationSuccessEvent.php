<?php

namespace App\Security\Event;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent as LexikAuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessEvent extends LexikAuthenticationSuccessEvent
{
    protected string $apiKeyId;

    public function __construct(array $data, UserInterface $user, Response $response, int $apiKeyId)
    {
        $this->apiKeyId = $apiKeyId;
        parent::__construct($data, $user, $response);
    }

    public function getApiKeyId(): int
    {
        return $this->apiKeyId;
    }

    public function setApiKeyId(int $apiKeyId): AuthenticationSuccessEvent
    {
        $this->apiKeyId = $apiKeyId;
        return $this;
    }
}
