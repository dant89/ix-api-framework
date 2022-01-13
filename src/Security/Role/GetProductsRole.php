<?php

namespace App\Security\Role;

class GetProductsRole extends AbstractRole
{
    public function __construct()
    {
        parent::__construct(RoleFactory::GET_PRODUCTS);
    }
}
