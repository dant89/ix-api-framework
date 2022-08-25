<?php

namespace App\Security\Role;

use InvalidArgumentException;

class RoleFactory
{
    // TODO add further roles as required, each role requires a Role file
    const GET_PRODUCT_OFFERING = 'GET_PRODUCT_OFFERING';
    const GET_PRODUCT_OFFERINGS = 'GET_PRODUCT_OFFERINGS';

    const ROLES = [
        self::GET_PRODUCT_OFFERING => GetProductOfferingRole::class,
        self::GET_PRODUCT_OFFERINGS => GetProductOfferingsRole::class,
    ];

    public function valid(string $role): bool
    {
        return array_key_exists($role, self::ROLES);
    }

    /**
     * Create a Role instance of a valid role
     *
     * @throw \InvalidArgumentException
     */
    public function role(string $role): AbstractRole
    {
        if (!$this->valid($role)) {
            throw new InvalidArgumentException("{$role} is not a valid role");
        }
        $cls = self::ROLES[$role];
        return new $cls();
    }
}
