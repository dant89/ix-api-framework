<?php

namespace App\Security\Authentication\EventListener;

use App\Security\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JwtCreatedListener
{
    protected TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();
        $payload = $event->getData();
        $payload['roles'] = $this->formatRoles($payload['roles']);
        $payload['sub'] = $user->getSubject()->getId();

        $event->setData($payload);
    }

    private function formatRoles(array $roles): array
    {
        $formatted = [];
        foreach ($roles as $role) {
            $formatted[] = (string) $role;
        }
        return $formatted;
    }
}
