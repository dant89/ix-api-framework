<?php

namespace App\Security\Role;

class GetProductRole extends AbstractRole
{
    public function __construct()
    {
        parent::__construct(RoleFactory::GET_PRODUCT);
    }
}
