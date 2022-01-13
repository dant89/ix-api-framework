<?php

namespace App\Security;

use App\Security\Entity\User;

class Security
{
    protected \Symfony\Component\Security\Core\Security $security;

    public function __construct(\Symfony\Component\Security\Core\Security $security)
    {
        $this->security = $security;
    }

    public function getSecurity(): \Symfony\Component\Security\Core\Security
    {
        return $this->security;
    }

    public function getUser(): ?User
    {
        $user = $this->security->getUser();
        if (get_class($user) !== User::class) {
            return null;
        }
        return $user;
    }
}
