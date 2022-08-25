<?php

namespace App\Security\Role;

abstract class AbstractRole
{
    private string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function __toString(): string
    {
        return $this->role;
    }
}
