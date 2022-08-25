<?php

namespace App\Security\Role;

class GetProductOfferingsRole extends AbstractRole
{
    public function __construct()
    {
        parent::__construct(RoleFactory::GET_PRODUCT_OFFERINGS);
    }
}
