<?php

namespace App\Security\Role;

class GetProductOfferingRole extends AbstractRole
{
    public function __construct()
    {
        parent::__construct(RoleFactory::GET_PRODUCT_OFFERING);
    }
}
