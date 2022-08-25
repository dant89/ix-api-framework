<?php

namespace App\ProductOffering\Entity;

class Mp2MpVcProductOffering extends ProductOffering
{
    public function __construct()
    {
        $this->type = 'mp2mp_vc';
    }
}
