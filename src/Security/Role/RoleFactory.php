<?php

namespace App\Security\Role;

use InvalidArgumentException;

class RoleFactory
{
    const GET_PRODUCT = 'GET_PRODUCT';
    const GET_PRODUCTS = 'GET_PRODUCTS';

    const ROLES = [
        self::GET_PRODUCT => GetProductRole::class,
        self::GET_PRODUCTS => GetProductsRole::class,
    ];

    public function valid(string $role): bool
    {
        return array_key_exists($role, self::ROLES);
    }

    public function role(string $role): AbstractRole
    {
        if (!$this->valid($role)) {
            throw new InvalidArgumentException("{$role} is not a valid role");
        }
        $cls = self::ROLES[$role];
        return new $cls();
    }
}
